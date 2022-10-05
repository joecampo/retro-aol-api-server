<?php

namespace App\Actions;

use App\Enums\ChatPacket;
use App\Models\Session;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Str;

class SendChatMessage
{
    use AsAction;

    public function handle(ConnectionInterface $connection, Session $session, string $message): void
    {
        if (! $message = Str::ascii($message)) {
            return;
        }

        with(ChatPacket::Aa_PACKET->value, function ($packet) use ($message, $connection) {
            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $messageLengthByte.bin2hex($message), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });
    }
}
