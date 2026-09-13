<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves the tenant by its own slug', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->get('/login?tenant='.$tenant->slug);

    $response->assertInertia(fn ($page) => $page->where('tenant.slug', $tenant->slug));
});

it('also resolves the tenant when the link uses crm_company_slug (the CRM widget link)', function () {
    $tenant = Tenant::factory()->create(['crm_company_slug' => 'acme-repairs']);

    $response = $this->get('/login?tenant=acme-repairs');

    $response->assertInertia(fn ($page) => $page->where('tenant.slug', $tenant->slug));
});

it('shows no tenant for an inactive tenant or an unknown slug', function () {
    Tenant::factory()->create(['slug' => 'inactive-co', 'active' => false]);

    $this->get('/login?tenant=inactive-co')
        ->assertInertia(fn ($page) => $page->where('tenant', null));

    $this->get('/login?tenant=does-not-exist')
        ->assertInertia(fn ($page) => $page->where('tenant', null));
});

it('stashes app/page context in session for the SSO redirect to pick up later', function () {
    $this->get('/login?tenant=whatever&app=inteteam_crm&page=%2Fcompany%2Forders%2F123');

    expect(session('support_widget_context'))->toBe([
        'app' => 'inteteam_crm',
        'page' => '/company/orders/123',
    ]);
});

it('does not stash widget context when app/page are absent', function () {
    $this->get('/login');

    expect(session('support_widget_context'))->toBeNull();
});
