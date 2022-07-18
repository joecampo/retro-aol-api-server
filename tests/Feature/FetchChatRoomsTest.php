<?php

use App\Actions\FetchChatRooms;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\ChatRoomList;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('it can fetch public chat rooms', function () {
    $session = Session::factory()->create();
    Event::fake();


    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        FetchChatRooms::run($connection, $session);
    });

    sleep(.1);

    Event::assertDispatched(ChatRoomList::class, function ($event) {
        return $event->chatRooms === [
            'deadend' => 0,
            'Welcome' => 7,
            'The 8-bit Guy' => 0,
            'Tech Linked' => 0,
            'Nostalgia Nerd' => 0,
            'News' => 0,
        ];
    });
});
