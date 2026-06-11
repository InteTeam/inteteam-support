# Ticket System — Feature Overview

## User Stories

**End Customer**
- As a customer, I can open a support ticket describing my issue so a repair shop can help me.
- As a customer, I can attach app/page context so engineers know exactly where the problem occurred.
- As a customer, I can view my open tickets and read replies from the shop.

**Tenant Admin (repair shop staff)**
- As a tenant admin, I can see all tickets raised by my customers.
- As a tenant admin, I can filter tickets by status and category to prioritise work.
- As a tenant admin, I can add notes to a ticket to communicate updates to the customer.
- As a tenant admin, I receive an email when a ticket's status changes.

**InteTeam Engineer**
- As an engineer, I can see all tickets across all tenants.
- As an engineer, I can filter by tenant, status, and category.
- As an engineer, I can assign a ticket to myself.
- As an engineer, I can add internal notes (hidden from tenant and customer).
- As an engineer, I can move tickets through the status workflow.

## Acceptance Criteria

### Ticket creation
- [ ] Customer or tenant admin can create a ticket with: `category` (hardware/software/billing/other), `description` (required, ≥ 10 chars), optional `app` and `page` context strings.
- [ ] Ticket is created in status `open` and linked to the correct `tenant_id` and `end_customer_id`.
- [ ] `tickets_per_month` usage counter is incremented on every successful create.
- [ ] When tenant is at 80% of monthly limit, the creation form shows a usage warning. Creation still succeeds.
- [ ] When tenant is at 100% of monthly limit, creation is blocked with HTTP 422.

### Status workflow
- [ ] Valid transitions: `open → in_progress`, `in_progress → resolved`, `resolved → closed`, `open → closed`.
- [ ] An email notification is queued whenever status changes.

### Notes
- [ ] Notes have `body` (required) and `is_internal` flag.
- [ ] Internal notes are only visible to engineers.
- [ ] Customers and tenant admins see only public notes.

### Access control
- [ ] Unauthenticated requests redirect to `/login`.
- [ ] Wrong role returns HTTP 403.
- [ ] Requesting a ticket from another tenant returns HTTP 404 (HasTenantScope hides it).

### Limits (from `plan_limits`)
| Tier | `tickets_per_month` |
|---|---|
| free | 20 |
| starter | 100 |
| pro | unlimited (0) |
