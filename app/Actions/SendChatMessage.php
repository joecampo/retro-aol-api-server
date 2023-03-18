<?php

namespace App\Actions;

use App\Enums\ChatPacket;
use App\Models\Session;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Support\Collection;

class SendChatMessage
{
    use AsAction;
    use WithAttributes;

    public function handle(ConnectionInterface $connection, Session $session, array $input): void
    {
        $this->set('session', $session);

        [$message, $id] = $input;

        if (! $message = Str::ascii($message)) {
            return;
        }

        with(ChatPacket::Aa_PACKET->value, function ($packet) use ($message, $connection) {
            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $messageLengthByte.bin2hex($message), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });

        $this->addMessageToCache($message, $id);
    }

    private function addMessageToCache(string $message, mixed $id = null): void
    {
        if (! $id) {
            return;
        }

        cache()->tags($this->session->id)->forever(
            'chat_messages',
            $this->messages()
                ->push([
                    'id' => $id,
                    'datetime' => now()->toString(),
                    'screenName' => cache()->tags($this->session->id)->get('screen_name'),
                    'message' => $message,
                ])
                ->values()
        );
    }

    private function messages(): Collection
    {
        return cache()->tags($this->session->id)->get('chat_messages', collect())->take(-100);
    }
}
