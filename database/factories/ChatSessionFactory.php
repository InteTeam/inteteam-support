<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatSession>
 */
class ChatSessionFactory extends Factory
{
    protected $model = ChatSession::class;

    public function definition(): array
    {
        return [
            'tenant_id'       => Tenant::factory(),
            'end_customer_id' => User::factory(),
            'agent_id'        => null,
            'status'          => 'queued',
            'ticket_id'       => null,
        ];
    }

    public function queued(): static
    {
        return $this->state(['status' => 'queued']);
    }

    public function active(int $agentId): static
    {
        return $this->state(['status' => 'active', 'agent_id' => $agentId]);
    }
}
