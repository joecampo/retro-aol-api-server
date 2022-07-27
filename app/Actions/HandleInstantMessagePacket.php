<?php

namespace App\Actions;

use App\Models\Session;
use App\ValueObjects\Atom;
use App\ValueObjects\Packet;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Events\NewInstantMessage;

class HandleInstantMessagePacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Session $session, Packet $packet): void
    {
        $this->set('session', $session);
        $this->set('packet', $packet);

        match (true) {
            $this->instantMessages()->has($this->globalId()) => $this->parseFromPrevious(),
            default => $this->parseFromNew()
        };
    }

    private function parseFromPrevious(): void
    {
        NewInstantMessage::dispatch(
            $this->session,
            $this->instantMessages()[$this->globalId()],
            $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary()
        );
    }

    private function parseFromNew(): void
    {
        with($this->instantMessages(), function (Collection $instantMessages): void {
            cache()->tags($this->session->id)->forever(
                'instant_messages',
                $instantMessages->put(
                    $this->globalId(),
                    $this->packet->atoms()->firstWhere('name', 'man_replace_data')->toBinary(),
                )
            );
        });

        NewInstantMessage::dispatch(
            $this->session,
            $this->packet->atoms()->firstWhere('name', 'man_replace_data')->toBinary(),
            $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary()
        );
    }

    private function globalId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_globalid';
            })?->data;
        });
    }

    private function instantMessages(): Collection
    {
        return cache()->tags($this->session->id)->get('instant_messages', collect());
    }
}
