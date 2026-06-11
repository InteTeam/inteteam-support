<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'tenant_id'       => Tenant::factory(),
            'end_customer_id' => User::factory(),
            'assigned_to'     => null,
            'app'             => null,
            'page'            => null,
            'category'        => $this->faker->randomElement(['hardware', 'software', 'billing', 'other']),
            'description'     => $this->faker->paragraph(),
            'status'          => 'open',
        ];
    }
}
