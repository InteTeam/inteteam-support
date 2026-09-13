<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeOtpTenant(): Tenant
{
    return Tenant::factory()->create(['crm_company_slug' => 'acme-repairs']);
}

// ---------------------------------------------------------------------------
// Request
// ---------------------------------------------------------------------------

it('always returns success on otp request regardless of whether the tenant exists', function () {
    Http::fake();

    $response = $this->post('/auth/customer/otp/request', [
        'tenant' => 'does-not-exist',
        'email' => 'someone@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('type', 'success');
});

it('relays the otp request to inteteam_crm using the tenant crm_company_slug', function () {
    config(['services.inteteam_crm.url' => 'http://crm.test']);
    $tenant = makeOtpTenant();

    Http::fake(['http://crm.test/*' => Http::response(['data' => ['message' => 'ok']])]);

    $this->post('/auth/customer/otp/request', [
        'tenant' => $tenant->slug,
        'email' => 'customer@example.com',
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'http://crm.test/api/v1/auth/customer/acme-repairs/otp/request'
            && $request['email'] === 'customer@example.com';
    });
});

it('also resolves the tenant when the CRM widget link uses crm_company_slug instead of the internal slug', function () {
    config(['services.inteteam_crm.url' => 'http://crm.test']);
    $tenant = makeOtpTenant();

    Http::fake(['http://crm.test/*' => Http::response(['data' => ['message' => 'ok']])]);

    // The "Open Support" widget only knows CRM's own company slug, not this
    // app's internal (randomly-suffixed) Tenant.slug -- see Tenant::findByLoginSlug().
    $this->post('/auth/customer/otp/request', [
        'tenant' => $tenant->crm_company_slug,
        'email' => 'customer@example.com',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'http://crm.test/api/v1/auth/customer/acme-repairs/otp/request');
});

// ---------------------------------------------------------------------------
// Verify
// ---------------------------------------------------------------------------

it('logs a new customer in on successful otp verification', function () {
    config(['services.inteteam_crm.url' => 'http://crm.test']);
    $tenant = makeOtpTenant();

    Http::fake(['http://crm.test/*' => Http::response([
        'data' => [
            'token' => 'jwt-token',
            'expires_in' => 3600,
            'customer' => ['id' => 'crm-cust-1', 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => null],
        ],
    ])]);

    $response = $this->post('/auth/customer/otp/verify', [
        'tenant' => $tenant->slug,
        'email' => 'jane@example.com',
        'code' => '123456',
    ]);

    $response->assertRedirect(route('customer.dashboard'));
    $this->assertTrue(auth()->check());
    $this->assertSame('end_customer', auth()->user()->role);
    $this->assertSame($tenant->id, auth()->user()->tenant_id);
});

it('rejects verification when crm returns no customer', function () {
    config(['services.inteteam_crm.url' => 'http://crm.test']);
    $tenant = makeOtpTenant();

    Http::fake(['http://crm.test/*' => Http::response(['error' => 'invalid'], 422)]);

    $response = $this->post('/auth/customer/otp/verify', [
        'tenant' => $tenant->slug,
        'email' => 'jane@example.com',
        'code' => '000000',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('otp');
    $this->assertFalse(auth()->check());
});

it('rejects verification when the email already belongs to a user of a different tenant', function () {
    config(['services.inteteam_crm.url' => 'http://crm.test']);
    $tenantA = makeOtpTenant();
    $tenantB = Tenant::factory()->create(['crm_company_slug' => 'other-co']);
    User::factory()->customer($tenantA->id)->create(['email' => 'shared@example.com', 'tenant_id' => $tenantA->id]);

    Http::fake(['http://crm.test/*' => Http::response([
        'data' => [
            'customer' => ['id' => 'crm-cust-2', 'name' => 'Shared Person', 'email' => 'shared@example.com', 'phone' => null],
        ],
    ])]);

    $response = $this->post('/auth/customer/otp/verify', [
        'tenant' => $tenantB->slug,
        'email' => 'shared@example.com',
        'code' => '111111',
    ]);

    $response->assertSessionHasErrors('otp');
    $this->assertFalse(auth()->check());
});
