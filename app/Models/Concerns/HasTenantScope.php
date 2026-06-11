<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = request()->attributes->get('tenant');
            if ($tenant instanceof Tenant) {
                $builder->where('tenant_id', $tenant->id);
            }
        });
    }
}
