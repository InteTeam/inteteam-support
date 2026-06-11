<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ChatMessage $message,
    ) {}

    /** @return Channel[] */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("chat-session.{$this->message->session_id}")];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'session_id' => $this->message->session_id,
            'author'     => [
                'id'   => $this->message->author_id,
                'name' => $this->message->author?->name,
                'role' => $this->message->author?->role,
            ],
            'body'       => $this->message->body,
            'sent_at'    => $this->message->sent_at?->toIso8601String(),
        ];
    }
}
