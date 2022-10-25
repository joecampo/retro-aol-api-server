<?php

use App\ValueObjects\Packet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Session;
use Tests\TestPacket;
use App\Events\NewInstantMessage;
use App\Actions\HandleGlobalPacket;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->session = Session::factory()->create();
});

it('can receive instant messages', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::INSTANT_MESSAGE_RECEIVED->value);

    HandleGlobalPacket::run($this->session, $packet);

    Event::assertDispatched(NewInstantMessage::class);
});
