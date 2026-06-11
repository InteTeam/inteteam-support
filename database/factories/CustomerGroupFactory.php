<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerGroup>
 */
class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name'      => $this->faker->words(2, true),
            'features'  => [],
        ];
    }

    public function withChat(): static
    {
        return $this->state(['features' => ['chat' => true]]);
    }
}
