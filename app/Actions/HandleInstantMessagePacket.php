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

        $this->createMessageSession();

        NewInstantMessage::dispatch($this->session, $this->from(), $this->message());
    }

    private function createMessageSession(): void
    {
        with($this->messageSessions(), function (Collection $sessions) {
            if (! $sessions->firstWhere('screenName', $this->from())) {
                cache()->tags($this->session->id)->forever('instant_messages', $sessions->push([
                    'globalId' => $this->globalId(),
                    'responseId' => $sessions->count(),
                    'screenName' => $this->from(),
                ]));
            }
        });
    }

    private function from(): string
    {
        if ($screenName = $this->packet->atoms()->firstWhere('name', 'man_replace_data')?->toBinary()) {
            return $screenName;
        }

        return $this->messageSessions()->firstWhere(['responseId' => $this->responseId()])['screenName'];
    }

    private function message(): string
    {
        return $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary();
    }

    private function globalId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_globalid';
            })?->data;
        });
    }

    private function responseId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_response_id';
            })?->data;
        });
    }

    private function messageSessions(): Collection
    {
        return cache()->tags($this->session->id)->get('instant_messages', collect());
    }
}
