<?php

namespace App\Actions;

use App\Enums\AtomPacket;
use App\Enums\PacketToken;
use App\ValueObjects\Packet;
use Illuminate\Support\Stringable;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Session;
use App\Events\ChatRoomUsers;
use App\Events\NewChatMessage;
use App\Events\UserEnteredChat;
use App\Events\UserLeftChat;

class HandleChatPacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Session $session, Packet $packet): void
    {
        $this->set('session', $session);

        match ($packet->token()?->name) {
            PacketToken::AT->name => $this->parseAtomStream($packet),
            PacketToken::AB->name => $this->parseRoomMessage($packet),
            default => info($packet->toHex())
        };
    }

    private function parseAtomStream(Packet $packet): void
    {
        match (true) {
            $packet->isAtomPacket(AtomPacket::CHAT_ROOM_ENTER) => $this->parseEnter($packet),
            $packet->isAtomPacket(AtomPacket::CHAT_ROOM_LEAVE) => $this->parseLeave($packet),
            $packet->isAtomPacket(AtomPacket::CHAT_ROOM_PEOPLE) => $this->parsePeopleInRoom($packet),
            default => null
        };
    }

    private function parsePeopleInRoom(Packet $packet): void
    {
        $users = $packet->takeNumber(1)
            ->toStringableHex()
            ->matchFromPacket(AtomPacket::CHAT_ROOM_PEOPLE, 7)
            ->matchAll('/0b01(.*?)100b04/')
            ->map(fn ($hex) => hex2binary(str($hex)->substr(2)))
            ->filter();

        ChatRoomUsers::dispatch($this->session, $users->toArray());
    }

    private function parseRoomMessage(Packet $packet): void
    {
        [$screenName, $message] = $packet->takeNumber(1)
            ->toStringableHex()
            ->substr(20)
            ->whenStartsWith('4f6e6c696e65486f7374', function (Stringable $data) {
                return $data->replace('4f6e6c696e65486f737420', '4f6e6c696e65486f73740000');
            })
            ->whenContains('7f4f6e6c696e65486f73743a09', function (Stringable $data) {
                return $data->replace('7f4f6e6c696e65486f73743a09', '0a4f6e6c696e65486f73743a20');
            })
            ->replaceLast('0000', '|')
            ->explode('|')
            ->map(fn (string $data) => trim(utf8_encode(hex2binary($data))));

        NewChatMessage::dispatch($this->session, $screenName, $message);
    }

    private function parseEnter(Packet $packet): void
    {
        $screenName = $packet->takeNumber(1)
            ->toStringableHex()
            ->matchFromPacket(AtomPacket::CHAT_ROOM_ENTER, 3)
            ->substr(2)
            ->when(true, fn ($hex) => hex2binary($hex));

        UserEnteredChat::dispatch($this->session, $screenName);
    }

    private function parseLeave(Packet $packet): void
    {
        $screenName = $packet->takeNumber(1)
            ->toStringableHex()
            ->matchFromPacket(AtomPacket::CHAT_ROOM_LEAVE, 3)
            ->substr(2)
            ->when(true, fn ($hex) => hex2binary($hex));

        UserLeftChat::dispatch($this->session, $screenName);
    }


    public function isAtomPacket(Packet $packet, AtomPacket $enum): bool
    {
        return $packet->takeNumber(1)->toStringableHex()->is($enum->value);
    }
}
