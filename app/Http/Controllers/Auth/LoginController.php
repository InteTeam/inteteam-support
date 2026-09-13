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
        // "Open Support" widget, see docs/tasks.md Phase 1). No tenant param
        // -> no OTP option shown, staff/tenant_admin SSO login is unaffected
        // either way. See Tenant::findByLoginSlug() for why this accepts
        // both this app's own slug and CRM's crm_company_slug.
        $tenantSlug = $request->query('tenant');
        $tenant = Tenant::findByLoginSlug($tenantSlug);

        // The widget's SSO staff/tenant_admin user doesn't authenticate here
        // at all (they go through /auth/sso/redirect, which doesn't take a
        // tenant param -- their tenant is resolved from the SSO company_id
        // claim). Stash the app/page context in session so SsoController can
        // land them on a pre-filled ticket instead of the bare dashboard.
        // Cleared by SsoController after use so a later plain SSO login
        // (e.g. logging back in tomorrow) doesn't replay stale context.
        if ($request->query('app') || $request->query('page')) {
            $request->session()->put('support_widget_context', [
                'app' => $request->query('app'),
                'page' => $request->query('page'),
            ]);
        }

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
