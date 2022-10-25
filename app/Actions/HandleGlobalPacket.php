<?php

namespace App\Actions;

use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Session;
use App\Enums\AtomPacketEvent;

class HandleGlobalPacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Session $session, Packet $packet): void
    {
        $this->set('session', $session);

        match ($packet->token()) {
            'AT' => $this->parseAtomStream($packet),
            default => null
        };
    }

    private function parseAtomStream(Packet $packet): void
    {
        match (AtomPacketEvent::event($packet)) {
            AtomPacketEvent::INSTANT_MESSAGE => HandleInstantMessagePacket::run($this->session, $packet),
            default => null
        };
    }
}
