<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Session;

class LoginProgress implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
        public int $progress
    ) {
    }

    public function broadcastWith(): array
    {
        return ['progress' => $this->progress];
    }

    public function broadcastAs(): string
    {
        return 'login.progress';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.'. $this->session->uuid);
    }
}
