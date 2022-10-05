<?php

namespace App\Actions;

use App\Enums\AtomPacket;
use App\ValueObjects\Packet;
use Illuminate\Support\Stringable;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Session;
use App\Events\ChatRoomUsers;
use App\Events\NewChatMessage;
use App\Events\UserEnteredChat;
use App\Events\UserLeftChat;
use App\Enums\AtomPacketEvent;
use App\ValueObjects\Atom;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HandleChatPacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Session $session, Packet $packet): void
    {
        $this->set('session', $session);

        match ($packet->token()) {
            'AT' => $this->parseAtomStream($packet),
            'AB' => $this->parseRoomMessage($packet),
            default => info($packet->toHex())
        };
    }

    private function parseAtomStream(Packet $packet): void
    {
        match (AtomPacketEvent::event($packet)) {
            AtomPacketEvent::CHAT_ROOM_ENTER => $this->parseEnter($packet),
            AtomPacketEvent::CHAT_ROOM_LEAVE => $this->parseLeave($packet),
            AtomPacketEvent::INSTANT_MESSAGE => HandleInstantMessagePacket::run($this->session, $packet),
            AtomPacketEvent::CHAT_ROOM_PEOPLE => $this->parsePeopleInRoom($packet),
            default => null
        };
    }

    private function parsePeopleInRoom(Packet $packet): void
    {
        $users = $packet->atoms()->where('name', 'chat_add_user')->map(fn (Atom $atom) => $atom->toBinary());

        cache()->tags($this->session->id)->forever('chat_users', $users->values());

        ChatRoomUsers::dispatch($this->session, $users->values()->toArray());
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
            ->when(true, function (Stringable $string) {
                return str(wordwrap($string, 2, ' ', true));
            })
            ->replaceFirst(' 00 ', '|')
            ->replace(' ', '')
            ->explode('|')
            ->map(fn (string $data) => trim(utf8_encode(hex2binary($data))));

        $this->addMessageToCache($screenName, $message);

        NewChatMessage::dispatch($this->session, $this->id(), $screenName, $message);
    }

    private function parseEnter(Packet $packet): void
    {
        $screenName = $packet->atoms()->firstWhere('name', 'chat_add_user')->toBinary();

        cache()->tags($this->session->id)->forever('chat_users', $this->users()->push($screenName)->values());

        $this->addMessageToCache('OnlineHost', "{$screenName} has entered the room.");

        UserEnteredChat::dispatch($this->session, $this->id(), $screenName);
    }

    private function parseLeave(Packet $packet): void
    {
        $screenName = $packet->atoms()->firstWhere('name', 'man_get_index_by_title')->toBinary();

        if (!$this->users()->contains($screenName)) {
            return;
        }

        cache()->tags($this->session->id)->forever(
            'chat_users',
            $this->users()->filter(fn ($user): bool => $user !== $screenName)->values()
        );

        $this->addMessageToCache('OnlineHost', "{$screenName} has left the room.");

        UserLeftChat::dispatch($this->session, $this->id(), $screenName);
    }

    public function isAtomPacket(Packet $packet, AtomPacket $enum): bool
    {
        return $packet->takeNumber(1)->toStringableHex()->is($enum->value);
    }

    private function addMessageToCache(string $screenName, string $message): void
    {
        cache()->tags($this->session->id)->forever(
            'chat_messages',
            $this->messages()
                ->push([
                    'id' => $this->id(),
                    'datetime' => now()->toString(),
                    'screenName' => $screenName,
                    'message' => $message,
                ])
                ->values()
        );
    }

    private function messages(): Collection
    {
        return cache()->tags($this->session->id)->get('chat_messages', collect())->take(-100);
    }

    private function users(): Collection
    {
        return cache()->tags($this->session->id)->get('chat_users', collect());
    }

    private function id(): string
    {
        return once(fn () => (string) Str::orderedUuid());
    }
}
