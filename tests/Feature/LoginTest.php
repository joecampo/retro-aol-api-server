<?php

use App\Actions\Login;
use Illuminate\Support\Facades\Event;
use App\Events\LoggedOn;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\SetScreenName;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('can sign on as a guest', function () {
    Event::fake();
    $session = Session::factory()->create();

    $this->client->connect(function ($connection) use ($session) {
        Login::run($connection, $session, ['guest', null]);
    });

    sleep(.1);

    Event::assertDispatched(SetScreenName::class);
    Event::assertDispatched(LoggedOn::class);
});
