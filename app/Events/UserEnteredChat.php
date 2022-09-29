<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Session;

class UserEnteredChat implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
        public string $id,
        public string $screenName,
    ) {
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->id, 'datetime' => now()->toString(), 'screenName' => $this->screenName];
    }

    public function broadcastAs(): string
    {
        return 'user.entered.chat';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.' . $this->session->uuid);
    }
}
