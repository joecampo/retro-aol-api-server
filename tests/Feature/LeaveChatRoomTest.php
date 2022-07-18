<!-- <?php

use React\Socket\ConnectionInterface;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Actions\LeaveChatRoom;
use function Clue\React\Block\sleep;

uses(RefreshDatabase::class);

it('it can leave a chat room', function () {
    $session = Session::factory()->create();

    $this->client->connect(function (ConnectionInterface $connection) use ($session) {
        LeaveChatRoom::run($connection, $session, 'vb');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2ac300127f7fa072440022000100000e0276620002000d');
});
