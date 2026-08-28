<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function __construct(
        private readonly SsoService $ssoService,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! config('sso.enabled')) {
            abort(404);
        }

        $pkce = $this->ssoService->generatePkce();
        $state = Str::random(40);

        $request->session()->put('sso_code_verifier', $pkce['verifier']);
        $request->session()->put('sso_state', $state);

        return redirect($this->ssoService->buildAuthorizationUrl($pkce['challenge'], $state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('sso_state');
        if (! $expectedState || ! hash_equals($expectedState, (string) $request->input('state', ''))) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Invalid state. Please try again.']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')
                ->withErrors(['sso' => $request->input('error_description', 'SSO authentication denied.')]);
        }

        $codeVerifier = $request->session()->pull('sso_code_verifier');

        try {
            $tokens = $this->ssoService->exchangeCode(
                (string) $request->input('code'),
                (string) $codeVerifier,
            );
            $claims = $this->ssoService->getUserInfo($tokens['access_token']);
        } catch (\Throwable $e) {
            Log::error('SSO callback failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO authentication failed. Please try again.']);
        }

        // sso_role is inteteam_sso's platform-identity field (root/company_admin/user)
        // -- NOT the same as a company-scoped "role" claim, and there never was an
        // "inteteam_staff"/"tenant_admin"/"end_customer" claim value on the wire. See
        // inte-playbook/architecture/sso-role-claims-and-support-auth.md. Also:
        // inteteam_sso has no end-customer concept at all -- a tenant's own customers
        // are never inteteam_sso users, so there is no third branch here. Customer
        // login is email-OTP only, handled entirely by CustomerOtpController.
        $ssoRole = $claims['sso_role'] ?? '';

        // root → InteTeam's own staff, engineer dashboard. No tenant context needed.
        if ($ssoRole === 'root') {
            $user = $this->findOrCreateUser($claims, 'engineer');
            Auth::login($user, false);
            $request->session()->regenerate();
            $this->storeSsoTokens($request, $tokens);

            return redirect()->route('engineer.dashboard')
                ->with(['alert' => 'Signed in via SSO.', 'type' => 'success']);
        }

        // company_admin or plain user → tenant portal. Any company_user gets tenant
        // portal access here; this app doesn't (yet) distinguish admin from member
        // within a tenant company.
        if ($ssoRole === 'company_admin' || $ssoRole === 'user') {
            $ssoCompanyId = $claims['company_id'] ?? null;
            $tenant = $ssoCompanyId ? Tenant::where('sso_company_id', $ssoCompanyId)->first() : null;

            if (! $tenant) {
                Log::warning('SSO callback: no Tenant found for sso_company_id', [
                    'sso_company_id' => $ssoCompanyId,
                    'email' => $claims['email'],
                ]);

                return redirect()->route('login')
                    ->withErrors(['sso' => 'Your company is not yet set up on Inte.Team Support. Contact your administrator.']);
            }

            $user = $this->findOrCreateUser($claims, 'tenant_admin');
            Auth::login($user, false);
            $request->session()->regenerate();
            $this->storeSsoTokens($request, $tokens);
            $request->session()->put('current_tenant_id', $tenant->id);

            return redirect()->route('tenant.dashboard')
                ->with(['alert' => 'Signed in via SSO.', 'type' => 'success']);
        }

        return redirect()->route('login')
            ->withErrors(['sso' => 'Unrecognised SSO role: ' . $ssoRole]);
    }

    /** @param array{access_token: string, refresh_token?: string, expires_in?: int} $tokens */
    private function storeSsoTokens(Request $request, array $tokens): void
    {
        $request->session()->put('sso_access_token', $tokens['access_token']);
        $request->session()->put('sso_refresh_token', $tokens['refresh_token'] ?? null);
        $request->session()->put('sso_token_expires_at', now()->addSeconds((int) ($tokens['expires_in'] ?? 3600))->timestamp);
    }

    /** @param array{email: string, name: string} $claims */
    private function findOrCreateUser(array $claims, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $claims['email']],
            ['name' => $claims['name'], 'role' => $role, 'password' => ''],
        );

        if ($user->name !== $claims['name']) {
            $user->update(['name' => $claims['name']]);
        }

        return $user;
    }
}
