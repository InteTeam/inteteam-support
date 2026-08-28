<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Relays customer email-OTP login to inteteam_crm's existing, live
 * CustomerOtpService -- inteteam_sso has no concept of a tenant's own end
 * customers (see inte-playbook/architecture/sso-role-claims-and-support-auth.md
 * §3), so this app never authenticates them via SSO claims.
 */
class CustomerOtpRelayService
{
    /**
     * Request an OTP be emailed to the customer. Always returns true --
     * mirrors CRM's own anti-enumeration behaviour, and there is nothing
     * useful to distinguish for the caller regardless: CRM never reveals
     * whether the email exists.
     */
    public function requestOtp(Tenant $tenant, string $email): bool
    {
        $crmUrl = $this->baseUrl();
        $slug = $tenant->crm_company_slug;

        if ($crmUrl === '' || $slug === null) {
            Log::warning('Customer OTP request: tenant not linked to a CRM company', [
                'tenant_id' => $tenant->id,
            ]);

            return true;
        }

        try {
            Http::timeout(5)->post("{$crmUrl}/api/v1/auth/customer/{$slug}/otp/request", [
                'email' => $email,
            ]);
        } catch (\Throwable $e) {
            Log::warning('inteteam_crm OTP request call failed', ['error' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * Verify an OTP code against CRM.
     *
     * @return array{id: string, name: string, email: string, phone: string|null}|null
     */
    public function verifyOtp(Tenant $tenant, string $email, string $code): ?array
    {
        $crmUrl = $this->baseUrl();
        $slug = $tenant->crm_company_slug;

        if ($crmUrl === '' || $slug === null) {
            return null;
        }

        try {
            $response = Http::timeout(5)->post("{$crmUrl}/api/v1/auth/customer/{$slug}/otp/verify", [
                'email' => $email,
                'code' => $code,
            ]);
        } catch (\Throwable $e) {
            Log::warning('inteteam_crm OTP verify call failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json('data.customer');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.inteteam_crm.url', ''), '/');
    }
}
