<?php

declare(strict_types=1);

use App\Events\ChatSessionQueued;
use App\Jobs\ConvertChatToTicket;
use App\Models\ChatSession;
use App\Models\CustomerGroup;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeEngineerForChat(): User
{
    return User::factory()->engineer()->create();
}

function makeChatTenant(): Tenant
{
    return Tenant::factory()->create([
        'plan_limits' => ['chat_sessions_per_month' => 50],
    ]);
}

function makeGroupWithChat(Tenant $tenant): CustomerGroup
{
    return CustomerGroup::factory()->withChat()->create(['tenant_id' => $tenant->id]);
}

function makeChatCustomer(Tenant $tenant, CustomerGroup $group): User
{
    return User::factory()->create([
        'role'               => 'end_customer',
        'tenant_id'          => $tenant->id,
        'customer_group_id'  => $group->id,
    ]);
}

// ---------------------------------------------------------------------------
// Guest redirected to login
// ---------------------------------------------------------------------------

it('redirects guest from support chat to login', function () {
    $this->get('/support/chat')->assertRedirect('/login');
});

it('redirects guest from engineer chat to login', function () {
    $this->get('/engineer/chat')->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Wrong role → 403
// ---------------------------------------------------------------------------

it('returns 403 when customer accesses engineer chat queue', function () {
    $tenant = makeChatTenant();
    $group = makeGroupWithChat($tenant);
    $customer = makeChatCustomer($tenant, $group);

    $this->actingAs($customer)
        ->get('/engineer/chat')
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Chat queue broadcast
// ---------------------------------------------------------------------------

it('broadcasts ChatSessionQueued when customer starts a session', function () {
    Event::fake([ChatSessionQueued::class]);
    Queue::fake();

    $tenant = makeChatTenant();
    $group = makeGroupWithChat($tenant);
    $customer = makeChatCustomer($tenant, $group);

    $this->actingAs($customer)
        ->post('/support/chat')
        ->assertRedirect();

    Event::assertDispatched(ChatSessionQueued::class);
});

// ---------------------------------------------------------------------------
// ConvertChatToTicket dispatched with 60s delay
// ---------------------------------------------------------------------------

it('dispatches ConvertChatToTicket job with 60s delay when session starts', function () {
    Event::fake();
    Bus::fake();

    $tenant = makeChatTenant();
    $group = makeGroupWithChat($tenant);
    $customer = makeChatCustomer($tenant, $group);

    $this->actingAs($customer)
        ->post('/support/chat');

    Bus::assertDispatched(ConvertChatToTicket::class);
});

// ---------------------------------------------------------------------------
// Auto-ticket fallback
// ---------------------------------------------------------------------------

it('ConvertChatToTicket job creates a ticket when session is still queued', function () {
    $tenant = makeChatTenant();
    $customer = User::factory()->create(['role' => 'end_customer', 'tenant_id' => $tenant->id]);

    $session = ChatSession::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'status'          => 'queued',
    ]);

    $job = new ConvertChatToTicket($session);
    $job->handle();

    $session->refresh();
    expect($session->status)->toBe('converted_to_ticket');
    expect($session->ticket_id)->not->toBeNull();

    $this->assertDatabaseHas('tickets', [
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
    ]);
});

it('ConvertChatToTicket job does nothing when session is already active', function () {
    $engineer = makeEngineerForChat();
    $tenant = makeChatTenant();
    $customer = User::factory()->create(['role' => 'end_customer', 'tenant_id' => $tenant->id]);

    $session = ChatSession::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'status'          => 'active',
        'agent_id'        => $engineer->id,
    ]);

    $job = new ConvertChatToTicket($session);
    $job->handle();

    $session->refresh();
    expect($session->status)->toBe('active');
});

// ---------------------------------------------------------------------------
// Counter increments on accept
// ---------------------------------------------------------------------------

it('increments chat_sessions_per_month on accept', function () {
    $engineer = makeEngineerForChat();
    $tenant = makeChatTenant();
    $customer = User::factory()->create(['role' => 'end_customer', 'tenant_id' => $tenant->id]);

    $session = ChatSession::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'status'          => 'queued',
    ]);

    $this->actingAs($engineer)
        ->post("/engineer/chat/{$session->id}/accept")
        ->assertRedirect();

    $this->assertDatabaseHas('usage_counters', [
        'tenant_id' => $tenant->id,
        'metric'    => 'chat_sessions_per_month',
        'period'    => now()->format('Y-m'),
        'count'     => 1,
    ]);
});

// ---------------------------------------------------------------------------
// Group access gate
// ---------------------------------------------------------------------------

it('blocks customer without chat feature from starting a session', function () {
    Queue::fake();

    $tenant = makeChatTenant();
    // Group WITHOUT chat enabled
    $group = CustomerGroup::factory()->create(['tenant_id' => $tenant->id, 'features' => []]);
    $customer = makeChatCustomer($tenant, $group);

    $this->actingAs($customer)
        ->post('/support/chat')
        ->assertSessionHasErrors('chat');
});

it('allows customer with chat feature to start a session', function () {
    Event::fake();
    Queue::fake();

    $tenant = makeChatTenant();
    $group = makeGroupWithChat($tenant);
    $customer = makeChatCustomer($tenant, $group);

    $this->actingAs($customer)
        ->post('/support/chat')
        ->assertRedirect();

    $this->assertDatabaseHas('chat_sessions', [
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customer->id,
        'status'          => 'queued',
    ]);
});

// ---------------------------------------------------------------------------
// Cross-tenant isolation → 404
// ---------------------------------------------------------------------------

it('engineer accept returns 404 for non-existent session', function () {
    $engineer = makeEngineerForChat();

    $this->actingAs($engineer)
        ->post('/engineer/chat/nonexistent-id/accept')
        ->assertNotFound();
});

it('customer cannot send message to another customer session', function () {
    $tenant = makeChatTenant();
    $group = makeGroupWithChat($tenant);
    $customerA = makeChatCustomer($tenant, $group);
    $customerB = makeChatCustomer($tenant, $group);
    $engineer = makeEngineerForChat();

    $session = ChatSession::factory()->create([
        'tenant_id'       => $tenant->id,
        'end_customer_id' => $customerB->id,
        'status'          => 'active',
        'agent_id'        => $engineer->id,
    ]);

    $this->actingAs($customerA)
        ->post("/support/chat/{$session->id}/messages", ['body' => 'hello'])
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Feature gate — block at 100%
// ---------------------------------------------------------------------------

it('blocks chat session start when tenant is at monthly limit', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create([
        'plan_limits' => ['chat_sessions_per_month' => 1],
    ]);
    $group = makeGroupWithChat($tenant);
    $customer = makeChatCustomer($tenant, $group);

    // Exhaust the limit
    \App\Models\UsageCounter::create([
        'tenant_id' => $tenant->id,
        'metric'    => 'chat_sessions_per_month',
        'period'    => now()->format('Y-m'),
        'count'     => 1,
    ]);

    $this->actingAs($customer)
        ->post('/support/chat')
        ->assertSessionHasErrors('chat');
});
