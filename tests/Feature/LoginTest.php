<?php

use App\Actions\Login;
use Illuminate\Support\Facades\Event;
use App\Events\LoggedOn;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\SetScreenName;
use App\Events\LoginProgress;
use App\Events\LoginInvalid;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->session = Session::factory()->create());

it('can sign on as a guest', function () {
    Event::fake();

    $this->client->connect(function ($connection) {
        Login::run($connection, $this->session, ['Guest', null]);
    });

    sleep(.1);

    Event::assertDispatched(LoginProgress::class, 4);
    Event::assertDispatched(SetScreenName::class);
    Event::assertDispatched(LoggedOn::class);
});

it('can sign on with a username and password', function () {
    Event::fake();

    $this->client->connect(function ($connection) {
        Login::run($connection, $this->session, ['AzureDiamond', 'hunter2']);
    });

    sleep(.1);

    Event::assertDispatched(LoggedOn::class);
});

it('can receive an invalid username and password', function () {
    Event::fake();
    $this->server->returnInvalidLogin = true;

    $this->client->connect(function ($connection) {
        Login::run($connection, $this->session, ['AzureDiamond', 'hunter3']);
    });

    sleep(.1);

    Event::assertDispatched(LoginInvalid::class);
});

it('requires that a username is valid', function () {
    test()->actingAs($this->session, 'sanctum')->postJson('/api/login', [
        'username' => '',
        'password' => 'hunter2'
    ])->assertStatus(422)->assertJson([
        'errors' => [
            'username' => [
                'The username field is required.'
            ]
        ]
    ]);

    test()->actingAs($this->session, 'sanctum')->postJson('/api/login', [
        'username' => 'a',
        'password' => 'hunter2'
    ])->assertStatus(422)->assertJson([
        'errors' => [
            'username' => [
                'The username must be at least 2 characters.'
            ]
        ]
    ]);
});

it('requires that a password is valid', function () {
    test()->actingAs($this->session, 'sanctum')->postJson('/api/login', [
        'username' => 'abc',
        'password' => 000,
    ])->assertStatus(422)->assertJson([
        'errors' => [
            'password' => [
                'The password must be a string.'
            ]
        ]
    ]);

    test()->actingAs($this->session, 'sanctum')->postJson('/api/login', [
        'username' => 'abc',
        'password' => 'a',
    ])->assertStatus(422)->assertJson([
        'errors' => [
            'password' => [
                'The password must be at least 4 characters.'
            ]
        ]
    ]);

    test()->actingAs($this->session, 'sanctum')->postJson('/api/login', [
        'username' => 'abc',
        'password' => '12345678901234567',
    ])->assertStatus(422)->assertJson([
        'errors' => [
            'password' => [
                'The password must not be greater than 16 characters.'
            ]
        ]
    ]);
});
