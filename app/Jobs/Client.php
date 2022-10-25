<?php

namespace App\Jobs;

use App\Actions\HandleGlobalPacket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use App\Actions\Login;
use App\Models\Session;
use App\Actions\StartHeartbeat;
use React\Socket\ServerInterface;
use React\Socket\SocketServer;
use App\Events\LoginInvalid;
use Illuminate\Support\Facades\Event;
use App\ValueObjects\Packet;
use function Clue\React\Block\sleep;

class Client implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const HOST = 'americaonline.reaol.org:5190';

    public bool $disconnect = false;

    public function __construct(
        public Session $session,
        public array $credentials
    ) {
    }

    public function handle(): void
    {
        $this->listenForCommands();

        $this->registerEventListeners();

        $this->connect();

        $this->loop();
    }

    private function connect(): void
    {
        with(new Connector(), function (Connector $connector) {
            $connector->connect(self::HOST)->then(function (ConnectionInterface $connection) {
                $this->connection = $connection;

                $connection->on('data', function ($data) {
                    if (app()->environment('local')) {
                        info(bin2hex($data));
                    }

                    HandleGlobalPacket::run($this->session, Packet::make($data));
                });

                $connection->on('close', function () {
                    $this->disconnect = true;
                });

                Login::run($connection, $this->session, $this->credentials);
                StartHeartbeat::run($connection);
            });
        });
    }

    private function listenForCommands(): void
    {
        $this->server()->on('connection', function (ConnectionInterface $connection): void {
            $connection->on('data', function ($data): void {
                with(json_decode($data), function (object $command): void {
                    resolve($command->action)->run($this->connection, $this->session, $command->payload);
                });
            });
        });
    }

    private function registerEventListeners(): void
    {
        Event::listen(LoginInvalid::class, function () {
            $this->disconnect = true;
        });
    }

    private function server(): ServerInterface
    {
        return retry(5, function () {
            $port = mt_rand(7000, 7999);

            return with(new SocketServer('127.0.0.1:'.$port), function (ServerInterface $server) use ($port) {
                $this->session->update(['command_port' => $port]);

                return $server;
            });
        });
    }

    private function loop(): void
    {
        while ($this->disconnect === false) {
            sleep(1);
        }
    }
}
