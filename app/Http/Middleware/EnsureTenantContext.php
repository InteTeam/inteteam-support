<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // tenant_admin: tenant set in session by SSO callback
        // end_customer: tenant bound to user record
        $tenantId = $request->session()->get('current_tenant_id')
            ?? $request->user()?->tenant_id;

        if (! $tenantId) {
            abort(403, 'No tenant context.');
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $request->session()->forget('current_tenant_id');
            abort(403, 'Tenant not found.');
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
