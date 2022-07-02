<?php

use App\Enums\AtomPacket;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can match to an atom stream packet and grab a match position', function () {
    $match = Packet::make(TestPacket::cQ_AT_PACKET->value)->takeNumber(1)
        ->toStringableHex()
        ->matchFromPacket(AtomPacket::CHAT_ROOM_PEOPLE, 5);

    expect($match->value)->toBe('0757656c636f6d65');
});
