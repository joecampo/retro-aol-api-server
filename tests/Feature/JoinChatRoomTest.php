<?php

use React\Socket\ConnectionInterface;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Actions\JoinChatRoom;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('it can join a chat room', function () {
    $session = Session::factory()->create();

    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        JoinChatRoom::run($connection, $session, 'vb');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5ac20800197f7fa0635100200001000107040000000403010276620002000d');
});
