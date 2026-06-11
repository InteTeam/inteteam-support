<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RemoteSession;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RemoteSession>
 */
class RemoteSessionFactory extends Factory
{
    protected $model = RemoteSession::class;

    public function definition(): array
    {
        return [
            'tenant_id'    => Tenant::factory(),
            'ticket_id'    => Ticket::factory(),
            'engineer_id'  => User::factory()->engineer(),
            'customer_id'  => User::factory(),
            'session_token' => null,
            'status'        => 'requested',
            'started_at'    => null,
            'ended_at'      => null,
            'duration_minutes' => 0,
        ];
    }

    public function requested(): static
    {
        return $this->state(['status' => 'requested']);
    }

    public function accepted(): static
    {
        return $this->state(['status' => 'accepted']);
    }

    public function active(): static
    {
        return $this->state([
            'status'     => 'active',
            'started_at' => now(),
        ]);
    }

    public function ended(int $minutes = 5): static
    {
        return $this->state([
            'status'           => 'ended',
            'started_at'       => now()->subMinutes($minutes),
            'ended_at'         => now(),
            'duration_minutes' => $minutes,
        ]);
    }
}
