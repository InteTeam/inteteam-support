# Ticket System — Architecture

## Data model

```
tenants (ULID PK)
  └─ tickets (ULID PK, tenant_id FK, end_customer_id → users, assigned_to → users nullable)
       └─ ticket_notes (ULID PK, ticket_id FK, author_id → users)
```

`users.tenant_id` — set for `end_customer` role (from SSO `tenant_id` claim); null for engineers and tenant_admins.

## Tenant isolation

`Ticket` and `TicketNote` use the `HasTenantScope` trait. The trait adds a global scope that filters by `tenant_id` when `request()->attributes->get('tenant')` is a `Tenant` instance. This is set by `EnsureTenantContext` middleware on all `portal/*` and `support/*` routes.

Engineer routes (`engineer/*`) do **not** apply `EnsureTenantContext`, so the global scope is inactive and they see all tickets.

Because queries are simply scoped out, a tenant requesting another tenant's ticket gets a 404 (ModelNotFoundException), not a 403. Tests assert 404.

## Request lifecycle

```
Customer POST /support/tickets
  → EnsureTenantContext (reads user->tenant_id, sets request attr)
  → CustomerTicketController::store
    → TicketService::create(tenant, customer, data)
      → UsageCounterService::isWithinLimit(tenant, 'tickets_per_month')   [block at 100%]
      → UsageCounterService::getUsagePercent(tenant, 'tickets_per_month') [warn at 80%]
      → Ticket::create(...)
      → UsageCounterService::increment(tenant, 'tickets_per_month')
      → returns [ticket, warning?]
  → 201 JSON / redirect
```

## Status transitions

```
open ──→ in_progress ──→ resolved ──→ closed
  └──────────────────────────────────→ closed
```

`TicketService::updateStatus` validates the transition, persists it, then dispatches `SendTicketStatusNotification`.

## Email notifications

`SendTicketStatusNotification` (queued job):
- Recipient: `end_customer_id` + `tenant_admin` users for the tenant
- Transport: app SMTP (MAIL_* env keys)
- Template: plain text for now; Phase 2+ will add HTML

## Feature gate thresholds

| % used | Behaviour |
|---|---|
| < 80 | Normal |
| 80–99 | Warning returned in response (`usage_warning` key) |
| 100 | HTTP 422 `tickets_per_month_limit_reached` |
