<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use React\Socket\Connector;

class Command
{
    public static function dispatch(string $action, mixed $payload = null): void
    {
        if (!self::port()) {
            return;
        }

        with(new Connector(), function (Connector $connector) use ($action, $payload): void {
            $connector->connect('127.0.0.1:'. self::port())->then(function ($connection) use ($action, $payload): void {
                $connection->write(json_encode(['action' => $action, 'payload' => $payload]));

                $connection->end();
            });
        });
    }

    private static function port(): ?int
    {
        return Auth::user()->command_port;
    }
}
