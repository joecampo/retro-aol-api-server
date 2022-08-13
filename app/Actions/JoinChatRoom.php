<?php

namespace App\Actions;

use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use App\Enums\ChatPacket;

class JoinChatRoom
{
    use AsAction;
    use WithAttributes;

    public function handle(ConnectionInterface $connection, Session $session, $roomName): void
    {
        $this->set('connection', $connection);
        $this->set('session', $session);
        $this->set('roomName', $roomName);

        with(ChatPacket::cQ_PACKET->value, function ($packet): void {
            $roomNameLengthByte = str_pad(dechex(strlen($this->roomName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $roomNameLengthByte.bin2hex($this->roomName), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });

        $connection->on('data', function (string $data): void {
            HandleChatPacket::run($this->session, Packet::make($data));
        });
    }
}
