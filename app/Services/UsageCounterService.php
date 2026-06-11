<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\UsageCounter;
use Illuminate\Support\Carbon;

class UsageCounterService
{
    public function increment(Tenant $tenant, string $metric, int $by = 1): UsageCounter
    {
        $period = Carbon::now()->format('Y-m');

        $counter = UsageCounter::firstOrCreate(
            ['tenant_id' => $tenant->id, 'metric' => $metric, 'period' => $period],
            ['count' => 0],
        );

        $counter->increment('count', $by);

        return $counter->fresh();
    }

    public function reset(Tenant $tenant, string $period): void
    {
        UsageCounter::where('tenant_id', $tenant->id)
            ->where('period', $period)
            ->update(['count' => 0, 'last_reset_at' => now()]);
    }

    public function isWithinLimit(Tenant $tenant, string $metric): bool
    {
        $limit = $tenant->getLimit($metric);

        // 0 = unlimited
        if ($limit === 0) {
            return true;
        }

        $period  = Carbon::now()->format('Y-m');
        $counter = UsageCounter::where('tenant_id', $tenant->id)
            ->where('metric', $metric)
            ->where('period', $period)
            ->first();

        $current = $counter ? $counter->count : 0;

        return $current < $limit;
    }

    public function getUsage(Tenant $tenant, string $metric, ?string $period = null): int
    {
        $period ??= Carbon::now()->format('Y-m');

        return (int) UsageCounter::where('tenant_id', $tenant->id)
            ->where('metric', $metric)
            ->where('period', $period)
            ->value('count');
    }

    public function getUsagePercent(Tenant $tenant, string $metric): float
    {
        $limit = $tenant->getLimit($metric);
        if ($limit === 0) {
            return 0.0;
        }

        return $this->getUsage($tenant, $metric) / $limit * 100.0;
    }
}
