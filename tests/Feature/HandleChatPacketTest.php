<?php

use App\Actions\HandleChatPacket;
use App\ValueObjects\Packet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Session;
use Tests\TestPacket;
use App\Events\ChatRoomUsers;
use App\Events\NewChatMessage;
use App\Events\UserEnteredChat;
use App\Events\UserLeftChat;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->session = Session::factory()->create();
});

it('can parse people that are in the chat room', function () {
    Event::fake();
    $packet = Packet::make(TestPacket::cQ_AT_PACKET->value);

    HandleChatPacket::run($this->session, $packet);

    Event::assertDispatched(ChatRoomUsers::class, function ($event) {
        return $event->users === [
            'PoSsE4uS',
            'Zip',
            'Guest6ZE',
            'Xak',
            'Guest9',
            'GuestP2YT'
        ];
    });
});

it('can parse new messages in the chat room', function () {
    Event::fake();
    $packet = Packet::make(TestPacket::AB_PACKET->value);

    HandleChatPacket::run($this->session, $packet);

    Event::assertDispatched(NewChatMessage::class, function ($event) {
        return $event->message === 'hi' && $event->screenName === 'Guest2';
    });
});

it('can parse user leaving chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_LEFT_AT->value);

    HandleChatPacket::run($this->session, $packet);

    Event::assertDispatched(UserLeftChat::class, function ($event) {
        return $event->screenName === 'Guest3L4U';
    });
});

it('can parse user entering the chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);

    HandleChatPacket::run($this->session, $packet);

    Event::assertDispatched(UserEnteredChat::class, function ($event) {
        return $event->screenName === 'GuestL';
    });
});
