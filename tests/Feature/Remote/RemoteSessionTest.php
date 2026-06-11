<?php

declare(strict_types=1);

use App\Jobs\IncrementRemoteMinutes;
use App\Jobs\SendRemoteSessionRequest;
use App\Models\CustomerGroup;
use App\Models\RemoteSession;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UsageCounter;
use App\Services\RemoteSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeRemoteTenant(): Tenant
{
    return Tenant::factory()->create([
        'plan_limits' => ['remote_minutes_per_month' => 300],
    ]);
}

function makeRemoteEngineer(): User
{
    return User::factory()->engineer()->create();
}

function makeRemoteGroup(Tenant $tenant): CustomerGroup
{
    return CustomerGroup::factory()->create([
        'tenant_id' => $tenant->id,
        'features'  => ['remote' => true],
    ]);
}

function makeRemoteCustomer(Tenant $tenant, CustomerGroup $group): User
{
    return User::factory()->create([
        'role'              => 'end_customer',
        'tenant_id'         => $tenant->id,
        'customer_group_id' => $group->id,
    ]);
}

function makeInProgressTicket(Tenant $tenant, User $customer, User $engineer): Ticket
{
    return Ticket::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'assigned_to'     => $engineer->id,
        'status'          => 'in_progress',
    ]);
}

// ---------------------------------------------------------------------------
// Guest redirected
// ---------------------------------------------------------------------------

it('redirects guest from engineer remote to login', function () {
    $this->get('/engineer/remote/fake-ticket-id/request')->assertRedirect('/login');
});

it('redirects guest from customer remote respond to login', function () {
    $this->get('/support/remote/fake-id/respond')->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Wrong role → 403
// ---------------------------------------------------------------------------

it('returns 403 when customer tries to request a remote session', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);

    $this->actingAs($customer)
        ->post('/engineer/remote/fake-ticket-id/request')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Session request creates row + dispatches notification job
// ---------------------------------------------------------------------------

it('requestSession creates a remote_sessions row and dispatches SendRemoteSessionRequest', function () {
    Bus::fake();
    Http::fake(['*' => Http::response(['token' => 'test-jwt'], 200)]);

    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $this->actingAs($engineer)
        ->post("/engineer/remote/{$ticket->id}/request")
        ->assertRedirect();

    Bus::assertDispatched(SendRemoteSessionRequest::class);
    $this->assertDatabaseHas('remote_sessions', [
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customer->id,
        'status'      => 'requested',
    ]);
});

// ---------------------------------------------------------------------------
// Session request blocked on in_progress ticket restriction
// ---------------------------------------------------------------------------

it('blocks remote session request on an open ticket', function () {
    Http::fake(['*' => Http::response(['token' => 'jwt'], 200)]);
    Queue::fake();

    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();

    $ticket = Ticket::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'status'          => 'open',
    ]);

    $this->actingAs($engineer)
        ->post("/engineer/remote/{$ticket->id}/request")
        ->assertSessionHasErrors('remote');
});

// ---------------------------------------------------------------------------
// Customer accept / decline
// ---------------------------------------------------------------------------

it('customer can accept a remote session request', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $session = RemoteSession::factory()->create([
        'tenant_id'   => $tenant->id,
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customer->id,
        'status'      => 'requested',
    ]);

    $this->actingAs($customer)
        ->get("/support/remote/{$session->id}/respond?action=accept")
        ->assertRedirect();

    $session->refresh();
    expect($session->status)->toBe('accepted');
});

it('customer can decline a remote session request', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $session = RemoteSession::factory()->create([
        'tenant_id'   => $tenant->id,
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customer->id,
        'status'      => 'requested',
    ]);

    $this->actingAs($customer)
        ->get("/support/remote/{$session->id}/respond?action=decline")
        ->assertRedirect();

    $session->refresh();
    expect($session->status)->toBe('declined');
});

// ---------------------------------------------------------------------------
// Cross-tenant isolation → 404
// ---------------------------------------------------------------------------

it('customer cannot respond to another customer session', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customerA = makeRemoteCustomer($tenant, $group);
    $customerB = makeRemoteCustomer($tenant, $group);
    $engineer  = makeRemoteEngineer();
    $ticket    = makeInProgressTicket($tenant, $customerB, $engineer);

    $session = RemoteSession::factory()->create([
        'tenant_id'   => $tenant->id,
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customerB->id,
        'status'      => 'requested',
    ]);

    // customerA tries to accept customerB's session
    $this->actingAs($customerA)
        ->get("/support/remote/{$session->id}/respond?action=accept")
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// Duration logging
// ---------------------------------------------------------------------------

it('IncrementRemoteMinutes increments counter and duration on active session', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $session = RemoteSession::factory()->create([
        'tenant_id'   => $tenant->id,
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customer->id,
        'status'      => 'active',
        'started_at'  => now(),
    ]);

    $job = new \App\Jobs\IncrementRemoteMinutes($session);
    $job->handle(app(\App\Services\RemoteSessionService::class));

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'remote_session_minutes',
        'count'     => 1,
    ]);

    $session->refresh();
    expect($session->duration_minutes)->toBe(1);
});

it('IncrementRemoteMinutes is a no-op when session has ended', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $session = RemoteSession::factory()->create([
        'tenant_id'   => $tenant->id,
        'ticket_id'   => $ticket->id,
        'engineer_id' => $engineer->id,
        'customer_id' => $customer->id,
        'status'      => 'ended',
        'ended_at'    => now(),
    ]);

    $job = new \App\Jobs\IncrementRemoteMinutes($session);
    $job->handle(app(\App\Services\RemoteSessionService::class));

    $this->assertDatabaseMissing('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'remote_session_minutes',
    ]);
});

// ---------------------------------------------------------------------------
// Group access gate
// ---------------------------------------------------------------------------

it('blocks session request when customer group has no remote feature', function () {
    Http::fake(['*' => Http::response(['token' => 'jwt'], 200)]);
    Queue::fake();

    $tenant   = makeRemoteTenant();
    $group    = CustomerGroup::factory()->create(['tenant_id' => $tenant->id, 'features' => []]);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    $service = app(RemoteSessionService::class);
    $this->expectException(\App\Exceptions\RemoteAccessDeniedException::class);
    $service->requestSession($ticket, $engineer);
});

// ---------------------------------------------------------------------------
// Feature gate at 100%
// ---------------------------------------------------------------------------

it('blocks session request when tenant is at monthly minutes limit', function () {
    Http::fake(['*' => Http::response(['token' => 'jwt'], 200)]);
    Queue::fake();

    $tenant = Tenant::factory()->create([
        'plan_limits' => ['remote_minutes_per_month' => 10],
    ]);
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);
    $engineer = makeRemoteEngineer();
    $ticket   = makeInProgressTicket($tenant, $customer, $engineer);

    UsageCounter::create([
        'tenant_id' => $tenant->id,
        'metric'    => 'remote_minutes_per_month',
        'period'    => now()->format('Y-m'),
        'count'     => 10,
    ]);

    $service = app(RemoteSessionService::class);
    $this->expectException(\App\Exceptions\RemoteLimitExceededException::class);
    $service->requestSession($ticket, $engineer);
});

// ---------------------------------------------------------------------------
// Agent registration counter
// ---------------------------------------------------------------------------

it('agent registration increments agents_registered counter', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);

    $this->postJson('/api/v1/agent/register', [
        'customer_id' => $customer->id,
        'tenant_id'   => $tenant->id,
    ])->assertOk()->assertJson(['registered' => true]);

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'agents_registered',
        'count'     => 1,
    ]);
});

it('agent registration is idempotent for the same customer', function () {
    $tenant   = makeRemoteTenant();
    $group    = makeRemoteGroup($tenant);
    $customer = makeRemoteCustomer($tenant, $group);

    $this->postJson('/api/v1/agent/register', ['customer_id' => $customer->id, 'tenant_id' => $tenant->id]);
    $this->postJson('/api/v1/agent/register', ['customer_id' => $customer->id, 'tenant_id' => $tenant->id]);

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'agents_registered',
        'count'     => 1,
    ]);
});
