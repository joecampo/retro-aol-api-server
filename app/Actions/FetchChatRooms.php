<?php

namespace App\Actions;

use App\Models\Session;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;
use App\ValueObjects\Packet;
use App\Enums\PacketToken;
use App\Enums\ChatPacket;
use React\EventLoop\Loop;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Events\ChatRoomList;
use App\Traits\RemoveListener;

class FetchChatRooms
{
    use AsAction;
    use WithAttributes;
    use RemoveListener;

    public function handle(ConnectionInterface $connection, Session $session)
    {
        $this->set('connection', $connection);
        $this->set('session', $session);

        $connection->write(Packet::make(ChatPacket::CJ_PACKET->value)->prepare());

        $connection->on('data', function (string $data) {
            with(Packet::make($data), function (Packet $packet) {
                if ($packet->token()?->name === PacketToken::AT->name) {
                    $this->parseChatRooms($packet);
                    $this->startTimer();
                }
            });
        });
    }

    private function parseChatRooms(Packet $packet): void
    {
        if (!str_contains($packet->toHex(), '0001000109032000620f13020102010a010101')) {
            return;
        }

        $chatRooms = $packet
            ->takeNumber(1)
            ->toStringableHex()
            ->after('010101000a')
            ->when(true, function ($hex) {
                preg_match_all('/06(\d{2,4})09(.*?)0202010201020001/', $hex, $output);

                return collect(array_combine($output[2], $output[1]))
                ->mapWithKeys(function ($people, $chatRoom) {
                    return [hex2binary($chatRoom) => intval(hex2binary($people))];
                });
            });

        ChatRoomList::dispatch($this->session, $chatRooms->toArray());
    }

    private function startTimer(): void
    {
        once(function () {
            Loop::addTimer(5, function () {
                $this->removeListener('data', $this->connection);
            });
        });
    }
}
