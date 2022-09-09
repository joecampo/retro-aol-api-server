<?php

namespace App\Actions;

use App\Models\Session;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;
use App\ValueObjects\Packet;
use App\Enums\ChatPacket;
use React\EventLoop\Loop;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Events\ChatRoomList;
use App\Traits\RemoveListener;
use App\ValueObjects\Atom;
use Illuminate\Support\Stringable;

class FetchChatRooms
{
    use AsAction;
    use WithAttributes;
    use RemoveListener;

    public function handle(ConnectionInterface $connection, Session $session)
    {
        $this->set('connection', $connection);
        $this->set('session', $session);

        $connection->write(Packet::make(ChatPacket::LB_PACKET->value)->prepare());

        $connection->on('data', function (string $data) {
            with(Packet::make($data), function (Packet $packet) {
                if ($packet->token() === 'AT') {
                    $this->parseChatRooms($packet);
                    $this->startTimer();
                }
            });
        });
    }

    private function parseChatRooms(Packet $packet): void
    {
        if ($packet->atoms()->firstWhere('name', 'man_set_context_globalid')?->data !== '32-98') {
            return;
        }

        $chatRooms = $packet->atoms()
            ->where('name', 'man_start_object')
            ->mapWithKeys(function (Atom $atom) {
                return with(str($atom->hex)->substr(2), function (Stringable $hex) {
                    return [hex2binary($hex->after('09')) => intval(hex2binary($hex->before('09')))];
                });
            });

        ChatRoomList::dispatch($this->session, $chatRooms->toArray());
    }

    private function startTimer(): void
    {
        once(function () {
            Loop::addTimer(10, function () {
                $this->removeListener('data', $this->connection);
            });
        });
    }
}
