<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewInstantMessage implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
        public string $screenName,
        public string $message
    ) {
    }

    public function broadcastWith(): array
    {
        return ['screenName' => $this->screenName, 'message' => $this->message];
    }

    public function broadcastAs(): string
    {
        return 'new.instant.message';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.' . $this->session->uuid);
    }
}
