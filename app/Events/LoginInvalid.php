<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Session;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoginInvalid implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
    ) {
    }

    public function broadcastWith(): array
    {
        return ['loginInvalid' => true];
    }

    public function broadcastAs(): string
    {
        return 'login.invalid';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.' . $this->session->uuid);
    }
}
