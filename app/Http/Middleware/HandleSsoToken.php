<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SsoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleSsoToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('sso.enabled') || ! Auth::check()) {
            return $next($request);
        }

        $expiresAt = $request->session()->get('sso_token_expires_at');

        if (! $expiresAt) {
            return $next($request);
        }

        if (now()->timestamp < (int) $expiresAt) {
            return $next($request);
        }

        $refreshToken = $request->session()->get('sso_refresh_token');

        if (! $refreshToken) {
            return $this->forceReLogin($request, 'Your session has expired. Please sign in again.');
        }

        try {
            /** @var SsoService $ssoService */
            $ssoService = app(SsoService::class);
            $tokens = $ssoService->refreshToken($refreshToken);

            $request->session()->put('sso_access_token', $tokens['access_token']);
            $request->session()->put('sso_refresh_token', $tokens['refresh_token'] ?? $refreshToken);
            $request->session()->put('sso_token_expires_at', now()->addSeconds((int) ($tokens['expires_in'] ?? 3600))->timestamp);

        } catch (\Throwable $e) {
            Log::info('SSO token refresh failed, logging user out.', ['error' => $e->getMessage()]);

            return $this->forceReLogin($request, 'Your session has expired. Please sign in again.');
        }

        return $next($request);
    }

    private function forceReLogin(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('login'));
        }

        return redirect()->route('login')->withErrors(['sso' => $message]);
    }
}
