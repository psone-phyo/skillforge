<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $conversation_id;

    public function __construct($user_id, $conversation_id)
    {
        $this->user_id = $user_id;
        $this->conversation_id = $conversation_id;
    }

    public function broadcastOn()
    {
        // Use same channel pattern as messages
        return new Channel('chat.' . $this->conversation_id);
    }

    public function broadcastAs()
    {
        return 'typing';
    }
}
