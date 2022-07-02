<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use App\Enums\SignOnState;
use App\Events\InvalidLogin;
use App\Events\LoggedOn;
use App\Traits\RemoveListener;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use App\Events\SetScreenName;
use App\Enums\AtomPacket;
use Illuminate\Support\Stringable;

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
            default => info($packet->toHex())
        };
    }

    private function needsDdPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_Dd_PACKET && $packet->toHex() === '5ab71100037f7f240d';
    }

    private function sendDdPacket(): void
    {
        with([$screenName, $password] = $this->credentials, function () use ($screenName, $password) {
            match ($screenName) {
                'guest' => $this->sendGuestDdPacket(),
                default => $this->sendAuthDdPacket($screenName, $password),
            };
        });

        $this->state = SignOnState::NEEDS_SC_PACKET;
    }

    private function sendGuestDdPacket(): void
    {
        $this->connection->write(Packet::make(AuthPacket::Dd_GUEST_PACKET->value)->prepare());
    }

    private function sendAuthDdPacket(string $screenName, string $password): void
    {
        with(AuthPacket::Dd_AUTH_PACKET->value, function ($packet) use ($screenName, $password) {
            $packet = str_replace('{screenName}', bin2hex($screenName), $packet);
            $packet = str_replace('{password}', bin2hex($password), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });
    }

    private function needsScPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC_PACKET && str_contains($packet->toHex(), '5343');
    }

    private function sendScPacket(): void
    {
        $this->connection->write(Packet::make((AuthPacket::SC_PACKET->value))->prepare());

        $this->state = SignOnState::AWAITING_WELCOME;
    }

    private function hasSuccessfulLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::AWAITING_WELCOME && str_contains($packet->data, 'Welcome');
    }

    private function hasInvalidLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC_PACKET
            && (str_contains($packet->data, 'incorrect!') || str_contains($packet->data, 'login-000002'));
    }

    private function handleSuccessfulLogin(): void
    {
        $this->state = SignOnState::ONLINE;

        $this->removeListener('data', $this->connection);

        LoggedOn::dispatch($this->session);
    }

    private function handleInvalidLogin(): void
    {
        $this->removeListener('data', $this->connection);

        // InvalidLogin::dispatch();
    }

    protected function sendVersionPacket(): void
    {
        $this->connection->write(hex2binary(AuthPacket::VERSION_PACKET->value));

        $this->state = SignOnState::NEEDS_Dd_PACKET;
    }

    private function setScreenName(Packet $packet): void
    {
        if (isset($this->screenName) || $this->state !== SignOnState::NEEDS_SC_PACKET) {
            return;
        }

        [$username,] = $this->credentials;

        if ($username !== 'guest') {
            $this->screenName = $username;

            SetScreenName::dispatch($this->session, $this->screenName);

            return;
        }

        if ($packet->isAtomPacket(AtomPacket::GUEST_WELCOME_WINDOW)) {
            with($packet->takeNumber(1)->toStringableHex(), function (Stringable $hex): void {
                $this->screenName = hex2binary($hex->matchFromPacket(AtomPacket::GUEST_WELCOME_WINDOW, 2));

                SetScreenName::dispatch($this->session, $this->screenName);
            });
        }
    }
}
