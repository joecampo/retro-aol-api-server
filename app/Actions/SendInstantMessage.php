<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\InstantMessagePacket;
use App\Models\Session;
use App\ValueObjects\Packet;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SendInstantMessage
{
    use AsAction;
    use WithAttributes;

    public function handle(ConnectionInterface $connection, Session $session, array $input): void
    {
        $this->set('session', $session);

        [$screenName, $message] = $input;

        if (! $screenName || ! $message) {
            return;
        }

        with(InstantMessagePacket::iS_PACKET->value, function ($packet) use ($screenName, $message, $connection) {
            $screenNameLengthByte = str_pad(dechex(strlen($screenName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{screenName}', $screenNameLengthByte.bin2hex($screenName), $packet);

            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{message}', $messageLengthByte.bin2hex($message), $packet);

            $sessionMessage = $this->findOrCreateMessageSession($screenName);

            $packet = str_replace('{streamId}', $sessionMessage['streamId'], $packet);

            $responseIdByte = str_pad(dechex($sessionMessage['responseId']), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{responseId}', $responseIdByte, $packet);

            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });
    }

    private function findOrCreateMessageSession(string $screenName): array
    {
        return with($this->messageSessions(), function (Collection $sessions) use ($screenName) {
            $session = $sessions->firstWhere('screenName', $screenName);

            if (! $session) {
                $session = $sessions->push([
                    'responseId' => $sessions->count(),
                    'streamId' => str_pad(dechex(mt_rand(500, 1001)), 4, '0', STR_PAD_LEFT),
                    'screenName' => $screenName,
                ])->last();

                cache()->tags($this->session->id)->forever('instant_messages', $sessions);
            }

            return $session;
        });
    }

    private function messageSessions(): Collection
    {
        return cache()->tags($this->session->id)->get('instant_messages', collect());
    }
}
