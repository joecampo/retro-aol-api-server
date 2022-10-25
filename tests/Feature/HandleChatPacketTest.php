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

    $cachedMessage = cache()->tags(Session::first()->id)->get('chat_messages')->first();

    expect(now()->parse($cachedMessage['datetime'])->format('Y-m-d'))->toBe(now()->format('Y-m-d'));

    expect($cachedMessage)->toMatchArray([
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

    $cachedMessage = cache()->tags(Session::first()->id)->get('chat_messages')->first();

    expect($cachedMessage)->toMatchArray([
        'screenName' => 'OnlineHost',
        'message' => 'Guest3L4U has left the room.'
    ]);

    Event::assertDispatched(UserLeftChat::class, function ($event) {
        return $event->screenName === 'Guest3L4U';
    });
});

it('does not parse leaves for users not currently in chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_LEFT_AT->value);

    HandleChatPacket::run($this->session, $packet);

    expect(cache()->tags($this->session->id)->get('chat_users'))->toBe(null);

    Event::assertNotDispatched(UserLeftChat::class, function ($event) {
        return $event->screenName === 'Guest3L4U';
    });
});

it('can parse user entering the chat room', function () {
    Event::fake();

    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);

    HandleChatPacket::run($this->session, $packet);

    expect(cache()->tags($this->session->id)->get('chat_users')->toArray())->toBe(['Guest5']);

    $cachedMessage = cache()->tags(Session::first()->id)->get('chat_messages')->first();

    expect($cachedMessage)->toMatchArray([
        'screenName' => 'OnlineHost',
        'message' => 'Guest5 has entered the room.'
    ]);

    Event::assertDispatched(UserEnteredChat::class, function ($event) {
        return $event->screenName === 'Guest5';
    });
});
