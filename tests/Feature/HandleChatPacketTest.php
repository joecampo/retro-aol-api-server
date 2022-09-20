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
use App\Events\NewInstantMessage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->session = Session::factory()->create();
});

it('can parse people that are in the chat room', function () {
    Event::fake();
    $packet = Packet::make(TestPacket::cQ_AT_PACKET->value);

    HandleChatPacket::run($this->session, $packet);

    expect(cache()->tags($this->session->id)->get('chat_users')->toArray())->toBe([
        'reaol',
        'PoSsE4uS',
        'Zip',
        'Guest6ZE',
        'Xak',
        'Guest9',
        'GuestP2YT'
    ]);

    Event::assertDispatched(ChatRoomUsers::class, function ($event) {
        return $event->users === [
            'reaol',
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

    expect(cache()->tags(Session::first()->id)->get('chat_messages')->count())->toBe(1);

    expect(cache()->tags(Session::first()->id)->get('chat_messages')->first())->toMatchArray([
        'screenName' => 'Guest2',
        'message' => 'hi'
    ]);
});

it('can parse user leaving chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_LEFT_AT->value);
    cache()->tags($this->session->id)->forever('chat_users', collect(['Guest3L4U']));

    HandleChatPacket::run($this->session, $packet);

    expect(cache()->tags($this->session->id)->get('chat_users')->toArray())->toBe([]);

    Event::assertDispatched(UserLeftChat::class, function ($event) {
        return $event->screenName === 'Guest3L4U';
    });
});

it('can parse user entering the chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);

    HandleChatPacket::run($this->session, $packet);

    expect(cache()->tags($this->session->id)->get('chat_users')->toArray())->toBe(['Guest5']);

    Event::assertDispatched(UserEnteredChat::class, function ($event) {
        return $event->screenName === 'Guest5';
    });
});

it('can receive instant messages', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::INSTANT_MESSAGE_RECEIVED->value);

    HandleChatPacket::run($this->session, $packet);

    Event::assertDispatched(NewInstantMessage::class);
});
