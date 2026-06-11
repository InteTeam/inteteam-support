<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'slug',
        'name',
        'tier',
        'plan_limits',
        'billing_period_start',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'plan_limits'          => 'array',
            'billing_period_start' => 'date',
            'active'               => 'boolean',
        ];
    }

    public function customerGroups(): HasMany
    {
        return $this->hasMany(CustomerGroup::class);
    }

    public function usageCounters(): HasMany
    {
        return $this->hasMany(UsageCounter::class);
    }

    public function getLimit(string $metric): int
    {
        return (int) ($this->plan_limits[$metric] ?? 0);
    }
}
