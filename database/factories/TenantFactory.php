<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'slug'                 => Str::slug($name) . '-' . Str::random(4),
            'name'                 => $name,
            'tier'                 => 'starter',
            'plan_limits'          => ['tickets_per_month' => 100],
            'billing_period_start' => now()->startOfMonth(),
            'active'               => true,
        ];
    }

    public function free(): static
    {
        return $this->state(['tier' => 'free', 'plan_limits' => ['tickets_per_month' => 20]]);
    }

    public function pro(): static
    {
        return $this->state(['tier' => 'pro', 'plan_limits' => ['tickets_per_month' => 0]]);
    }
}
