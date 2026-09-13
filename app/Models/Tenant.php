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
        'sso_company_id',
        'crm_company_slug',
    ];

    protected function casts(): array
    {
        return [
            'plan_limits' => 'array',
            'billing_period_start' => 'date',
            'active' => 'boolean',
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

    /**
     * Resolve an active Tenant from a `?tenant=` login-link value, which may
     * be either this app's own `slug` or the CRM's `crm_company_slug`.
     *
     * The CRM's "Open Support" widget links here with its own company slug
     * (already known to CRM, no new cross-app ID to sync) rather than this
     * app's internal, randomly-suffixed `slug` (which CRM has no way to
     * learn without a new sync point). See docs/tasks.md Phase 1.
     */
    public static function findByLoginSlug(?string $slug): ?self
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        return self::where('active', true)
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('crm_company_slug', $slug))
            ->first();
    }
}
