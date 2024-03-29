<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use React\EventLoop\Loop;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;

class StartHeartbeat
{
    use AsAction;

    public function handle(ConnectionInterface $connection): void
    {
        with(Loop::get(), function (StreamSelectLoop $loop) use ($connection) {
            $loop->addPeriodicTimer(120, function () use ($connection) {
                // 5a ## ## 00 03 ## ## ## 0d
                $connection->write(hex2binary('5ac93300031847a60d'));
            });
        });
    }
}
