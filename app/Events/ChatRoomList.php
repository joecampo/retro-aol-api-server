<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomList implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Session $session,
        public array $chatRooms
    ) {
    }

    public function broadcastAs(): string
    {
        return 'chat.room.list';
    }

    public function broadcastWith(): array
    {
        return ['chatRooms' => $this->chatRooms];
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('client.' . $this->session->uuid);
    }
}
