# Live Chat — Architecture

## Data model

```
tenants
  └─ chat_sessions (ULID PK, tenant_id, end_customer_id → users,
                    agent_id → users nullable, status, ticket_id → tickets nullable)
       └─ chat_messages (ULID PK, session_id, author_id → users, body, sent_at)

users
  └─ customer_group_id → customer_groups (nullable, for end_customer role)
  └─ agent_availability (user_id, status: online/away/offline)
```

## WebSocket infrastructure — Laravel Reverb

Reverb runs as its own container (`support_reverb`) on port 8080. Nginx proxies
WebSocket upgrade requests (`/app/*`) to it. The frontend connects via Laravel Echo
(pusher-js driver) using `VITE_REVERB_*` env vars.

## Broadcast channels

| Channel | Type | Who subscribes |
|---|---|---|
| `chat-queue` | Private | All engineers — receives `ChatSessionQueued`, `SessionAccepted` |
| `chat-session.{id}` | Private | Engineer + customer of that session — receives `ChatMessageSent` |
| `agent-availability` | Private | Engineers — receives `AgentAvailabilityChanged` |

Channel authorization in `routes/channels.php`.

## Request lifecycle — customer starts chat

```
Customer POST /support/chat
  → ChatService::startSession(tenant, customer)
    → check customer group has chat feature
    → check isWithinLimit(tenant, 'chat_sessions_per_month')
    → ChatSession::create(status=queued)
    → broadcast ChatSessionQueued → chat-queue
    → ConvertChatToTicket::dispatch($session)->delay(60s)
  → 201 + session id
```

## Request lifecycle — engineer accepts

```
Engineer POST /engineer/chat/{session}/accept
  → ChatService::acceptSession(session, engineer)
    → DB::transaction + lockForUpdate
    → abort 409 if not queued
    → session.status = active, session.agent_id = engineer
    → UsageCounterService::increment(tenant, 'chat_sessions_per_month')
    → broadcast SessionAccepted
  → redirect to /engineer/chat/{session}
```

## Auto-ticket fallback

`ConvertChatToTicket` job fires 60 s after session creation.

```
if session.status === 'queued':
    build transcript from chat_messages
    Ticket::create(tenant_id, end_customer_id, description=transcript)
    session.status = converted_to_ticket
    session.ticket_id = ticket.id
```

Tickets created by auto-conversion do **not** consume the `tickets_per_month` quota.

## Group access gate

`ChatService::startSession` checks:
```php
$group = $customer->customerGroup;
if (!$group || !$group->hasFeature('chat')) {
    throw new ChatAccessDeniedException();
}
```

Tenant admin enables/disables chat per group via `Tenant/Groups/Index` UI →
`PATCH /portal/groups/{group}/features`.

## Feature gate thresholds

| % used | Behaviour |
|---|---|
| < 80 | Normal |
| 80–99 | `usage_warning` in response |
| 100 | HTTP 422 |
