<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SsoService
{
    private readonly string $ssoUrl;
    private readonly string $ssoInternalUrl;
    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $redirectUri;

    public function __construct()
    {
        $this->ssoUrl         = config('sso.url');
        $this->ssoInternalUrl = config('sso.internal_url');
        $this->clientId       = config('sso.client_id');
        $this->clientSecret   = config('sso.client_secret');
        $this->redirectUri    = config('sso.redirect_uri');
    }

    /** @return array{verifier: string, challenge: string} */
    public function generatePkce(): array
    {
        $verifier  = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return ['verifier' => $verifier, 'challenge' => $challenge];
    }

    public function buildAuthorizationUrl(string $codeChallenge, string $state): string
    {
        return $this->ssoUrl . '/oauth/authorize?' . http_build_query([
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'response_type'         => 'code',
            'scope'                 => '',
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    /** @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string} */
    public function exchangeCode(string $code, string $codeVerifier): array
    {
        $response = Http::asForm()->post($this->ssoInternalUrl . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
            'code_verifier' => $codeVerifier,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('SSO token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /** @return array{sub: string, email: string, name: string, role: string} */
    public function getUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get($this->ssoInternalUrl . '/oauth/userinfo');

        if (! $response->successful()) {
            throw new RuntimeException('SSO userinfo request failed: ' . $response->body());
        }

        return $response->json();
    }

    /** @return array{access_token: string, refresh_token: string, expires_in: int} */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->ssoInternalUrl . '/oauth/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('SSO token refresh failed: ' . $response->body());
        }

        return $response->json();
    }
}
