<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'      => $this->faker->name(),
            'email'     => $this->faker->unique()->safeEmail(),
            'password'  => '',
            'role'      => 'end_customer',
            'tenant_id' => null,
        ];
    }

    public function engineer(): static
    {
        return $this->state(['role' => 'engineer', 'tenant_id' => null]);
    }

    public function tenantAdmin(string $tenantId): static
    {
        return $this->state(['role' => 'tenant_admin', 'tenant_id' => null]);
    }

    public function customer(string $tenantId): static
    {
        return $this->state(['role' => 'end_customer', 'tenant_id' => $tenantId]);
    }
}
