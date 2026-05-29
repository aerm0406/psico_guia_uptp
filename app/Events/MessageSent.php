<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation;
        $recipientId = ($conversation->user_one_id === $this->message->sender_id) 
            ? $conversation->user_two_id 
            : $conversation->user_one_id;

        // Emitimos en el canal de conversación y en el canal global privado del receptor
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
            new PrivateChannel('App.Models.User.' . $recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'sender_id' => $this->message->sender_id,
            'time' => $this->message->created_at->format('h:i A'),
            'conversation_id' => $this->message->conversation_id
        ];
    }
}
