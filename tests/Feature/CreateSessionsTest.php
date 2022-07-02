<?php

//@codingStandardsIgnoreStart
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Session;
use function Pest\Laravel\{post};

uses(RefreshDatabase::class);

it('creates a new session and returns a token', function () {
    $response = post('/api/sessions');

    expect($response->getStatusCode())->toBe(201);

    expect(strlen($response->json()['token']))->toBe(42);
});


it('does not create more than one session per ip address', function () {
    post('/api/sessions');
    post('/api/sessions');

    expect(Session::count())->toBe(1);

    test()->withServerVariables(['REMOTE_ADDR' => '192.168.1.1']);

    post('/api/sessions');
    expect(Session::count())->toBe(2);
});
