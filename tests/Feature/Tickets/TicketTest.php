<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeEngineer(): User
{
    return User::factory()->engineer()->create();
}

function makeTenant(): Tenant
{
    return Tenant::factory()->create();
}

function makeTenantAdmin(Tenant $tenant): User
{
    $user = User::factory()->create(['role' => 'tenant_admin']);
    // session-based tenant context set in actingAs calls below
    return $user;
}

function makeCustomer(Tenant $tenant): User
{
    return User::factory()->customer($tenant->id)->create();
}

// ---------------------------------------------------------------------------
// Guest redirected to login
// ---------------------------------------------------------------------------

it('redirects guest from engineer tickets to login', function () {
    $this->get('/engineer/tickets')->assertRedirect('/login');
});

it('redirects guest from portal tickets to login', function () {
    $this->get('/portal/tickets')->assertRedirect('/login');
});

it('redirects guest from support tickets to login', function () {
    $this->get('/support/tickets')->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Wrong role → 403
// ---------------------------------------------------------------------------

it('returns 403 when end_customer accesses engineer tickets', function () {
    $tenant = makeTenant();
    $customer = makeCustomer($tenant);

    $this->actingAs($customer)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->get('/engineer/tickets')
        ->assertForbidden();
});

it('returns 403 when engineer accesses tenant portal', function () {
    $tenant = makeTenant();
    $engineer = makeEngineer();

    $this->actingAs($engineer)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->get('/portal/tickets')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Ticket created (assertDatabaseHas)
// ---------------------------------------------------------------------------

it('tenant admin can create a ticket', function () {
    Queue::fake();

    $tenant = makeTenant();
    $admin = makeTenantAdmin($tenant);

    $this->actingAs($admin)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->post('/portal/tickets', [
            'category'    => 'software',
            'description' => 'The login screen freezes on load.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'tenant_id' => $tenant->id,
        'category'  => 'software',
        'status'    => 'open',
    ]);
});

it('customer can create a ticket', function () {
    Queue::fake();

    $tenant = makeTenant();
    $customer = makeCustomer($tenant);

    $this->actingAs($customer)
        ->post('/support/tickets', [
            'category'    => 'hardware',
            'description' => 'My printer is not connecting to the network.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'category'        => 'hardware',
        'status'          => 'open',
    ]);
});

// ---------------------------------------------------------------------------
// Cross-tenant isolation → 404
// ---------------------------------------------------------------------------

it('tenant admin gets 404 when viewing another tenant ticket', function () {
    $tenantA = makeTenant();
    $tenantB = makeTenant();

    $adminA = makeTenantAdmin($tenantA);

    $ticketB = Ticket::factory()->create([
        'tenant_id'       => $tenantB->id,
        'end_customer_id' => makeCustomer($tenantB)->id,
    ]);

    // Admin from tenant A tries to view tenant B's ticket
    $this->actingAs($adminA)
        ->withSession(['current_tenant_id' => $tenantA->id])
        ->get("/portal/tickets/{$ticketB->id}")
        ->assertNotFound();
});

it('customer gets 404 when viewing another customer ticket in same tenant', function () {
    $tenant = makeTenant();
    $customerA = makeCustomer($tenant);
    $customerB = makeCustomer($tenant);

    $ticketB = Ticket::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customerB->id,
    ]);

    $this->actingAs($customerA)
        ->get("/support/tickets/{$ticketB->id}")
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// Status transitions
// ---------------------------------------------------------------------------

it('engineer can update ticket status from open to in_progress', function () {
    $engineer = makeEngineer();
    $tenant = makeTenant();
    $ticket = Ticket::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => makeCustomer($tenant)->id,
        'status'          => 'open',
    ]);

    $this->actingAs($engineer)
        ->patch("/engineer/tickets/{$ticket->id}/status", ['status' => 'in_progress'])
        ->assertRedirect();

    expect(Ticket::withoutGlobalScope('tenant')->find($ticket->id)->status)->toBe('in_progress');
});

it('rejects invalid status transition', function () {
    $engineer = makeEngineer();
    $tenant = makeTenant();
    $ticket = Ticket::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => makeCustomer($tenant)->id,
        'status'          => 'open',
    ]);

    $this->actingAs($engineer)
        ->patch("/engineer/tickets/{$ticket->id}/status", ['status' => 'resolved'])
        ->assertStatus(422);
});

// ---------------------------------------------------------------------------
// Counter increments
// ---------------------------------------------------------------------------

it('increments tickets_per_month counter on ticket creation', function () {
    Queue::fake();

    $tenant = makeTenant();
    $customer = makeCustomer($tenant);

    $this->actingAs($customer)
        ->post('/support/tickets', [
            'category'    => 'other',
            'description' => 'Counter increment test description here.',
        ]);

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'tickets_per_month',
        'period'    => now()->format('Y-m'),
        'count'     => 1,
    ]);
});

// ---------------------------------------------------------------------------
// Feature gate: block at 100%
// ---------------------------------------------------------------------------

it('blocks ticket creation when tenant has reached monthly limit', function () {
    $tenant = Tenant::factory()->create([
        'plan_limits' => ['tickets_per_month' => 1],
    ]);
    $customer = makeCustomer($tenant);

    // Burn the one allowed ticket
    \App\Models\UsageCounter::create([
        'tenant_id' => $tenant->id,
        'metric'    => 'tickets_per_month',
        'period'    => now()->format('Y-m'),
        'count'     => 1,
    ]);

    $this->actingAs($customer)
        ->post('/support/tickets', [
            'category'    => 'other',
            'description' => 'This should be blocked by the usage limit.',
        ])
        ->assertSessionHasErrors('limit');
});
