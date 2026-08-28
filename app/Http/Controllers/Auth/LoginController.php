<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(Request $request): Response
    {
        // Customer OTP login needs a tenant to scope the request/verify calls
        // to. There's no other way to identify the tenant pre-authentication
        // (no subdomain-per-tenant, no session) -- a link to /login?tenant=
        // {slug} is expected to come from the tenant's own CRM/portal (the
        // still-unbuilt "Open Support" widget, see docs/tasks.md Phase 1).
        // No tenant param -> no OTP option shown, staff/tenant_admin SSO
        // login is unaffected either way.
        $tenantSlug = $request->query('tenant');
        $tenant = $tenantSlug ? Tenant::where('slug', $tenantSlug)->where('active', true)->first() : null;

        return Inertia::render('Auth/Login', [
            'ssoEnabled' => config('sso.enabled'),
            'ssoUrl' => route('auth.sso.redirect'),
            'otpRequestUrl' => route('customer.otp.request'),
            'otpVerifyUrl' => route('customer.otp.verify'),
            'tenant' => $tenant ? ['slug' => $tenant->slug, 'name' => $tenant->name] : null,
            'tenantSlugRequested' => $tenantSlug,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
