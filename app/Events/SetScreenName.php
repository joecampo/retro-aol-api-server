<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SetScreenName implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
        public string $screenName
    ) {
    }

    public function broadcastWith(): array
    {
        return ['screenName' => $this->screenName];
    }

    public function broadcastAs(): string
    {
        return 'set.screen.name';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.' . $this->session->uuid);
    }
}
