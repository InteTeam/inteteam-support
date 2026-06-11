<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentAvailabilityChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $status,
    ) {}

    /** @return Channel[] */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('agent-availability')];
    }

    public function broadcastAs(): string
    {
        return 'availability.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'status'  => $this->status,
        ];
    }
}
