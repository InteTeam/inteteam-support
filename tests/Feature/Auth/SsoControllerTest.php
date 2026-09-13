<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function ssoTestConfig(): void
{
    config([
        'sso.enabled' => true,
        'sso.url' => 'http://sso.test',
        'sso.internal_url' => 'http://sso.test',
        'sso.client_id' => 'test-client',
        'sso.client_secret' => 'test-secret',
        'sso.redirect_uri' => 'http://localhost:8094/auth/sso/callback',
    ]);
}

function fakeSsoTokenAndUserinfo(array $userinfo): void
{
    Http::fake([
        'http://sso.test/oauth/token' => Http::response(['access_token' => 'tok', 'refresh_token' => 'ref', 'expires_in' => 3600]),
        'http://sso.test/oauth/userinfo' => Http::response($userinfo),
    ]);
}

// ---------------------------------------------------------------------------
// root → engineer dashboard
// ---------------------------------------------------------------------------

it('logs a root sso_role user in as engineer with no tenant required', function () {
    ssoTestConfig();
    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'staff@inte.team',
        'name' => 'InteTeam Staff',
        'sso_role' => 'root',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect(route('engineer.dashboard'));
    $this->assertTrue(auth()->check());
    $this->assertSame('engineer', auth()->user()->role);
});

// ---------------------------------------------------------------------------
// company_admin / user → tenant portal
// ---------------------------------------------------------------------------

it('logs a company_admin sso_role user in as tenant_admin and resolves the tenant by sso_company_id', function () {
    ssoTestConfig();
    $tenant = Tenant::factory()->create(['sso_company_id' => 'sso-company-1']);

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'admin@acme.example',
        'name' => 'Acme Admin',
        'company_id' => 'sso-company-1',
        'company_name' => 'Acme Repairs',
        'sso_role' => 'company_admin',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect(route('tenant.dashboard'));
    $this->assertTrue(auth()->check());
    $this->assertSame('tenant_admin', auth()->user()->role);
    $this->assertSame($tenant->id, session('current_tenant_id'));
});

it('logs a plain user sso_role user in as tenant_admin too', function () {
    ssoTestConfig();
    Tenant::factory()->create(['sso_company_id' => 'sso-company-2']);

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'member@acme.example',
        'name' => 'Acme Member',
        'company_id' => 'sso-company-2',
        'company_name' => 'Acme Repairs',
        'sso_role' => 'user',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect(route('tenant.dashboard'));
    $this->assertSame('tenant_admin', auth()->user()->role);
});

it('rejects company_admin login when no Tenant matches the sso_company_id', function () {
    ssoTestConfig();

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'admin@unprovisioned.example',
        'name' => 'Unprovisioned Admin',
        'company_id' => 'sso-company-unknown',
        'company_name' => 'Unknown Co',
        'sso_role' => 'company_admin',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect('/login');
    $this->assertFalse(auth()->check());
});

// ---------------------------------------------------------------------------
// end_customer is not reachable via SSO claims at all
// ---------------------------------------------------------------------------

it('rejects an unrecognised sso_role instead of treating it as end_customer', function () {
    ssoTestConfig();

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'someone@example.com',
        'name' => 'Someone',
        'sso_role' => 'end_customer',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect('/login');
    $this->assertStringContainsString('Unrecognised SSO role', session('errors')?->first('sso'));
    $this->assertFalse(auth()->check());
});

// ---------------------------------------------------------------------------
// CRM "Open Support" widget context (app/page stashed by LoginController)
// ---------------------------------------------------------------------------

it('redirects to a pre-filled new ticket when widget context was stashed in session', function () {
    ssoTestConfig();
    Tenant::factory()->create(['sso_company_id' => 'sso-company-3']);

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'admin@widget.example',
        'name' => 'Widget Admin',
        'company_id' => 'sso-company-3',
        'company_name' => 'Widget Co',
        'sso_role' => 'company_admin',
    ]);

    $this->withSession([
        'sso_state' => 'st',
        'sso_code_verifier' => 'vrf',
        'support_widget_context' => ['app' => 'inteteam_crm', 'page' => '/company/orders/123'],
    ]);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect(route('tenant.tickets.create', ['app' => 'inteteam_crm', 'page' => '/company/orders/123']));
    $this->assertNull(session('support_widget_context'), 'widget context must be consumed, not replayed on a later login');
});

it('falls back to the plain dashboard when no widget context was stashed', function () {
    ssoTestConfig();
    Tenant::factory()->create(['sso_company_id' => 'sso-company-4']);

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'admin@plain.example',
        'name' => 'Plain Admin',
        'company_id' => 'sso-company-4',
        'company_name' => 'Plain Co',
        'sso_role' => 'company_admin',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $response = $this->get('/auth/sso/callback?code=code&state=st');

    $response->assertRedirect(route('tenant.dashboard'));
});

// ---------------------------------------------------------------------------
// Existing behaviour preserved
// ---------------------------------------------------------------------------

it('aborts with 404 when sso is disabled', function () {
    config(['sso.enabled' => false]);

    $this->get('/auth/sso/redirect')->assertNotFound();
});

it('rejects callback when state is missing from session', function () {
    ssoTestConfig();

    $response = $this->get('/auth/sso/callback?code=abc&state=whatever');

    $response->assertRedirect('/login');
    $this->assertStringContainsString('Invalid state', session('errors')?->first('sso'));
});

it('syncs name from sso claims if changed', function () {
    ssoTestConfig();
    $user = User::factory()->engineer()->create(['email' => 'staff@inte.team', 'name' => 'Old Name']);

    fakeSsoTokenAndUserinfo([
        'sub' => 'sso-id',
        'email' => 'staff@inte.team',
        'name' => 'New Name',
        'sso_role' => 'root',
    ]);

    $this->withSession(['sso_state' => 'st', 'sso_code_verifier' => 'vrf']);
    $this->get('/auth/sso/callback?code=code&state=st');

    expect($user->fresh()->name)->toBe('New Name');
});
