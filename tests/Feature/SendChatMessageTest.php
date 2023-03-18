<?php

//@codingStandardsIgnoreStart
use App\Actions\SendChatMessage;
use React\Socket\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Session;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('it can send a message to chat', function () {
    $session = Session::factory()->create();

    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        SendChatMessage::run($connection, $session, ['hey abc what is up my friend', null]);
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a8136003a7f7fa04161012a0001000107040000001b010a040000010203011c686579206162632077686174206973207570206d7920667269656e640002000d');
});

it('it can send a message to chat with a specified id', function () {
    $session = Session::factory()->create();

    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        SendChatMessage::run($connection, $session, ['hey abc what is up my friend', 'aeeb348b-d28e-4c05-a98c-bcab53f5cbfb']);
    });

    sleep(.1);

    expect(cache()->tags($session->id)->get('chat_messages')->toArray())->toMatchArray([
        [
            'id' => 'aeeb348b-d28e-4c05-a98c-bcab53f5cbfb',
            'datetime' => now()->toString(),
            'screenName' => null,
            'message' => 'hey abc what is up my friend'
        ]
    ]);
});
