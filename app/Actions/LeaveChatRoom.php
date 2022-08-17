<?php

namespace App\Actions;

use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use App\Enums\ChatPacket;
use App\Traits\RemoveListener;

class LeaveChatRoom
{
    use AsAction;
    use WithAttributes;
    use RemoveListener;

    public function handle(ConnectionInterface $connection, Session $session, $roomName): void
    {
        $this->set('connection', $connection);
        $this->set('session', $session);
        $this->set('roomName', $roomName);

        with(ChatPacket::rD_PACKET->value, function ($packet): void {
            $roomNameLengthByte = str_pad(dechex(strlen($this->roomName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $roomNameLengthByte.bin2hex($this->roomName), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());

            if ($closure = $this->findClosure($this->connection, JoinChatRoom::class)) {
                $this->connection->removeListener('data', $closure);
            }
        });
    }
}
