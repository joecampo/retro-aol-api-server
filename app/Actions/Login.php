<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use App\Enums\SignOnState;
use App\Events\LoggedOn;
use App\Traits\RemoveListener;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use App\Events\SetScreenName;
use App\Events\LoginInvalid;
use Illuminate\Support\Stringable;
use App\Events\LoginProgress;

class Login
{
    use AsAction;
    use RemoveListener;
    use WithAttributes;

    protected SignOnState $state = SignOnState::OFFLINE;

    protected string $screenName;

    public function handle(ConnectionInterface $connection, Session $session, array $credentials): void
    {
        $this->set('connection', $connection);
        $this->set('session', $session);
        $this->set('credentials', $credentials);

        $this->sendVersionPacket();

        $connection->on('data', fn (string $data) => $this->processPacket(Packet::make($data)));
    }

    private function processPacket(Packet $packet): void
    {
        $this->setScreenName($packet);

        match (true) {
            $this->needsDdPacket($packet) => $this->sendDdPacket(),
            $this->needsScPacket($packet) => $this->sendScPacket(),
            $this->hasInvalidLogin($packet) => $this->handleInvalidLogin(),
            $this->hasSuccessfulLogin($packet) => $this->handleSuccessfulLogin(),
            $this->needsUdPAcket($packet) => $this->sendUdPacket(),
            default => info($packet->toHex())
        };
    }

    private function needsDdPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_Dd && $packet->takeNumber(1)->toHex() === '5ab71100037f7f240d';
    }

    private function sendDdPacket(): void
    {
        with([$screenName, $password] = $this->credentials, function () use ($screenName, $password) {
            match (strtolower($screenName)) {
                'guest' => $this->sendGuestDdPacket(),
                default => $this->sendAuthDdPacket($screenName, $password),
            };
        });

        LoginProgress::dispatch($this->session, 50);

        $this->state = SignOnState::NEEDS_SC;
    }

    private function sendGuestDdPacket(): void
    {
        $this->connection->write(Packet::make(AuthPacket::Dd_GUEST_PACKET->value)->prepare());
    }

    private function sendAuthDdPacket(string $screenName, string $password): void
    {
        with(AuthPacket::Dd_AUTH_PACKET->value, function ($packet) use ($screenName, $password) {
            $packet = str_replace('{screenName}', bin2hex(str_pad($screenName, 10, ' ', STR_PAD_RIGHT)), $packet);
            $packet = str_replace('{password}', bin2hex($password), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });
    }

    private function needsScPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC && str_contains($packet->toHex(), '5343');
    }

    private function sendScPacket(): void
    {
        $this->connection->write(Packet::make((AuthPacket::SC_PACKET->value))->prepare());

        LoginProgress::dispatch($this->session, 75);

        $this->state = SignOnState::AWAITING_WELCOME;
    }

    private function needsUdPAcket(Packet $packet): bool
    {
        return $packet->token() === 'AT' && str_contains($packet->toHex(), '7544');
    }

    private function sendUdPacket(): void
    {
        with(AuthPacket::uD_PACKET->value, function ($packet) {
            $packet = str_replace('{timestamp}', bin2hex(time()), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });
    }

    private function hasSuccessfulLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::AWAITING_WELCOME && str_contains($packet->data, 'Welcome');
    }

    private function hasInvalidLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC
            && (str_contains($packet->data, 'incorrect!') || str_contains($packet->data, 'login-000002'));
    }

    private function handleSuccessfulLogin(): void
    {
        $this->state = SignOnState::ONLINE;

        $this->removeListener('data', $this->connection);

        LoginProgress::dispatch($this->session, 100);
        LoggedOn::dispatch($this->session);
    }

    private function handleInvalidLogin(): void
    {
        $this->removeListener('data', $this->connection);

        LoginInvalid::dispatch($this->session);
    }

    protected function sendVersionPacket(): void
    {
        $this->connection->write(hex2binary(AuthPacket::VERSION_PACKET->value));

        LoginProgress::dispatch($this->session, 25);

        $this->state = SignOnState::NEEDS_Dd;
    }

    private function setScreenName(Packet $packet): void
    {
        if (isset($this->screenName) || $this->state !== SignOnState::NEEDS_SC) {
            return;
        }

        [$username,] = $this->credentials;

        if (strtolower($username) !== 'guest') {
            $this->screenName = $username;

            SetScreenName::dispatch($this->session, $this->screenName);

            return;
        }

        if ($packet->token() === 'AT' && $packet->atoms()->contains('name', 'act_set_guest_flag')) {
            with(str($packet->atoms()->firstWhere('name', 'man_append_data')->toBinary()), function (Stringable $text) {
                $this->screenName = $text->match('/, (.*?)</');

                SetScreenName::dispatch($this->session, $this->screenName);
            });
        }
    }
}
