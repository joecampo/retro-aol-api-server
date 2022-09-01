<?php

//@codingStandardsIgnoreStart
use App\Actions\SendInstantMessage;
use React\Socket\ConnectionInterface;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('can send an instant message', function () {
    $session = Session::factory()->create();

    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        SendInstantMessage::run($connection, $session, ['Guest356', 'how are you?']);
    });

    sleep(.1);
    expect($this->server->packet->toStringableHex()->is('5a2a2a00427f7fa06953*00010001070400000000010a04000000010301084775657374333536011d00010a040000000203010c686f772061726520796f753f011d000002000d'))->toBe(true);
});
