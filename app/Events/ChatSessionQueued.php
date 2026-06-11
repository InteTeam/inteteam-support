<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatSessionQueued implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ChatSession $session,
    ) {}

    /** @return Channel[] */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat-queue')];
    }

    public function broadcastAs(): string
    {
        return 'session.queued';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->session->id,
            'tenant_id'   => $this->session->tenant_id,
            'tenant_name' => $this->session->tenant?->name,
            'customer'    => [
                'id'   => $this->session->end_customer_id,
                'name' => $this->session->customer?->name,
            ],
            'queued_at'   => $this->session->created_at?->toIso8601String(),
        ];
    }
}
