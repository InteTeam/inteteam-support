<?php

declare(strict_types=1);

use App\Models\CustomerGroup;
use App\Models\Tenant;
use App\Models\UsageCounter;
use App\Models\User;
use App\Services\KbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeKbTenant(): Tenant
{
    return Tenant::factory()->create([
        'plan_limits' => ['tickets_per_month' => 100],
    ]);
}

function makeKbGroup(Tenant $tenant, bool $kbEnabled = true): CustomerGroup
{
    return CustomerGroup::factory()->create([
        'tenant_id' => $tenant->id,
        'features'  => $kbEnabled ? ['kb' => true] : [],
    ]);
}

function makeKbCustomer(Tenant $tenant, CustomerGroup $group): User
{
    return User::factory()->create([
        'role'              => 'end_customer',
        'tenant_id'         => $tenant->id,
        'customer_group_id' => $group->id,
    ]);
}

// ---------------------------------------------------------------------------
// KbService — querying RAG
// ---------------------------------------------------------------------------

it('returns articles from inteteam-rag and increments kb_lookups counter', function () {
    $tenant = makeKbTenant();

    Http::fake([
        '*' => Http::response([
            'articles' => [
                ['title' => 'Printer feed guide', 'url' => 'https://help.example.com/1', 'excerpt' => 'If your printer…'],
            ],
        ], 200),
    ]);

    $service  = app(KbService::class);
    $articles = $service->suggest($tenant, 'my printer is not feeding paper');

    expect($articles)->toHaveCount(1);
    expect($articles[0]['title'])->toBe('Printer feed guide');

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'kb_lookups',
        'count'     => 1,
    ]);
});

it('returns empty array when RAG is unavailable', function () {
    $tenant = makeKbTenant();

    Http::fake(['*' => Http::response(null, 500)]);

    $service  = app(KbService::class);
    $articles = $service->suggest($tenant, 'my printer is not feeding paper');

    expect($articles)->toBeEmpty();
    $this->assertDatabaseMissing('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'kb_lookups',
    ]);
});

it('skips the RAG query when the string is shorter than 10 characters', function () {
    $tenant = makeKbTenant();

    Http::fake();

    $service  = app(KbService::class);
    $articles = $service->suggest($tenant, 'short');

    expect($articles)->toBeEmpty();
    Http::assertNothingSent();
});

it('does not increment counter when RAG returns zero articles', function () {
    $tenant = makeKbTenant();

    Http::fake(['*' => Http::response(['articles' => []], 200)]);

    $service = app(KbService::class);
    $service->suggest($tenant, 'a very long query about something obscure');

    $this->assertDatabaseMissing('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'kb_lookups',
    ]);
});

// ---------------------------------------------------------------------------
// Customer KB suggest endpoint — feature gate
// ---------------------------------------------------------------------------

it('suggest endpoint returns empty array when group has kb disabled', function () {
    $tenant   = makeKbTenant();
    $group    = makeKbGroup($tenant, false); // kb off
    $customer = makeKbCustomer($tenant, $group);

    Http::fake(['*' => Http::response(['articles' => [['title' => 'Guide', 'url' => 'x', 'excerpt' => '']]], 200)]);

    $this->actingAs($customer)
        ->postJson('/support/kb/suggest', ['query' => 'my printer is not feeding paper'])
        ->assertOk()
        ->assertJson(['articles' => []]);

    Http::assertNothingSent(); // RAG not called
});

it('suggest endpoint returns articles when group has kb enabled', function () {
    $tenant   = makeKbTenant();
    $group    = makeKbGroup($tenant, true); // kb on
    $customer = makeKbCustomer($tenant, $group);

    Http::fake([
        '*' => Http::response([
            'articles' => [['title' => 'Fix it guide', 'url' => 'https://help.example.com/fix', 'excerpt' => 'Step 1…']],
        ], 200),
    ]);

    $this->actingAs($customer)
        ->postJson('/support/kb/suggest', ['query' => 'my printer is not feeding paper'])
        ->assertOk()
        ->assertJsonCount(1, 'articles')
        ->assertJsonPath('articles.0.title', 'Fix it guide');
});

it('suggest endpoint validates minimum query length', function () {
    $tenant   = makeKbTenant();
    $group    = makeKbGroup($tenant, true);
    $customer = makeKbCustomer($tenant, $group);

    $this->actingAs($customer)
        ->postJson('/support/kb/suggest', ['query' => 'short'])
        ->assertUnprocessable();
});

// ---------------------------------------------------------------------------
// Tenant KB suggest endpoint
// ---------------------------------------------------------------------------

it('tenant suggest endpoint returns articles regardless of group kb flag', function () {
    $tenant = makeKbTenant();
    $group  = makeKbGroup($tenant, false); // kb off for customers
    $admin  = User::factory()->create(['role' => 'tenant_admin', 'tenant_id' => $tenant->id]);

    Http::fake([
        '*' => Http::response([
            'articles' => [['title' => 'Admin guide', 'url' => 'https://help.example.com/admin', 'excerpt' => '…']],
        ], 200),
    ]);

    $this->actingAs($admin)
        ->postJson('/portal/kb/suggest', ['query' => 'my printer is not feeding paper'])
        ->assertOk()
        ->assertJsonCount(1, 'articles');
});

// ---------------------------------------------------------------------------
// Tenant group kb toggle
// ---------------------------------------------------------------------------

it('tenant can toggle kb feature on a group', function () {
    $tenant = makeKbTenant();
    $group  = makeKbGroup($tenant, false);
    $admin  = User::factory()->create(['role' => 'tenant_admin', 'tenant_id' => $tenant->id]);

    $this->actingAs($admin)
        ->patch("/portal/groups/{$group->id}/features", [
            'feature' => 'kb',
            'enabled' => true,
        ])
        ->assertRedirect();

    $group->refresh();
    expect($group->features['kb'])->toBeTrue();
});

// ---------------------------------------------------------------------------
// Unauthenticated access → redirect
// ---------------------------------------------------------------------------

it('redirects guest from suggest endpoint to login', function () {
    $this->postJson('/support/kb/suggest', ['query' => 'my printer is not feeding paper'])
        ->assertUnauthorized();
});
