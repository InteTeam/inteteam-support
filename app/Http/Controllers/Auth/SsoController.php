<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        $pkce  = $this->ssoService->generatePkce();
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

        $ssoRole = $claims['role'] ?? '';

        // inteteam_staff → engineer dashboard
        if ($ssoRole === 'inteteam_staff') {
            $user = $this->findOrCreateUser($claims, 'engineer');
            Auth::login($user, false);
            $request->session()->regenerate();
            $this->storeSsoTokens($request, $tokens);

            return redirect()->route('engineer.dashboard')
                ->with(['alert' => 'Signed in via SSO.', 'type' => 'success']);
        }

        // tenant_admin → tenant portal
        if ($ssoRole === 'tenant_admin') {
            $user = $this->findOrCreateUser($claims, 'tenant_admin');
            Auth::login($user, false);
            $request->session()->regenerate();
            $this->storeSsoTokens($request, $tokens);

            $tenantId = $claims['tenant_id'] ?? null;
            if ($tenantId) {
                $request->session()->put('current_tenant_id', $tenantId);
            }

            return redirect()->route('tenant.dashboard')
                ->with(['alert' => 'Signed in via SSO.', 'type' => 'success']);
        }

        // end_customer → customer view
        if ($ssoRole === 'end_customer') {
            $user = $this->findOrCreateUser($claims, 'end_customer');

            // Persist tenant binding if SSO provides it
            $tenantId = $claims['tenant_id'] ?? null;
            if ($tenantId && $user->tenant_id !== $tenantId) {
                $user->update(['tenant_id' => $tenantId]);
            }

            Auth::login($user, false);
            $request->session()->regenerate();
            $this->storeSsoTokens($request, $tokens);

            return redirect()->route('customer.dashboard')
                ->with(['alert' => 'Signed in via SSO.', 'type' => 'success']);
        }

        return redirect()->route('login')
            ->withErrors(['sso' => 'Unrecognised role: ' . $ssoRole]);
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
