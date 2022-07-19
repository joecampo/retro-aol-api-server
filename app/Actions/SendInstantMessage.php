<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\InstantMessagePacket;
use App\Models\Session;
use App\ValueObjects\Packet;
use React\Socket\ConnectionInterface;

class SendInstantMessage
{
    use AsAction;

    public function handle(ConnectionInterface $connection, Session $session, array $input): void
    {
        [$screenName, $message] = $input;

        if (! $screenName || ! $message) {
            return;
        }

        with(InstantMessagePacket::iS_PACKET->value, function ($packet) use ($screenName, $message, $connection) {
            $screenNameLengthByte = str_pad(dechex(strlen($screenName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{screenName}', $screenNameLengthByte.bin2hex($screenName), $packet);

            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{message}', $messageLengthByte.bin2hex($message), $packet);

            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });
    }
}
