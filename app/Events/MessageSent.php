<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // public $message;

    // public function __construct($message)
    // {
    //     $this->message = $message;
    // }

    // public function broadcastOn()
    // {
    //     return new Channel('my-channel');

    // }

    // public function broadcastAs()
    // {
    //     return 'my-event';
    // }
        public array $payload;
    public int $conversationId;

    public function __construct(Message $message)
    {
        $this->conversationId = $message->conversation_id;
        $this->payload = [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender' => [
                'id' => $message->sender?->id,
                'name' => $message->sender?->name,
                'avatar' => $message->sender?->profile_url ?? null,
            ],
            'body' => $message->message,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat.' . $this->conversationId);
    }

        public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
