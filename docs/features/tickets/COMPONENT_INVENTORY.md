# Ticket System — Component Inventory

## Backend

| Layer | File | Purpose |
|---|---|---|
| Migration | `database/migrations/…_add_tenant_id_to_users_table.php` | `users.tenant_id` FK for end_customers |
| Migration | `database/migrations/…_create_tickets_table.php` | tickets table |
| Migration | `database/migrations/…_create_ticket_notes_table.php` | ticket_notes table |
| Trait | `app/Models/Concerns/HasTenantScope.php` | Global scope filtering by current tenant |
| Model | `app/Models/Ticket.php` | HasUlids, HasTenantScope, relationships |
| Model | `app/Models/TicketNote.php` | HasUlids, belongs to Ticket |
| Policy | `app/Policies/TicketPolicy.php` | Engineer sees all; tenant sees own; customer sees own |
| Exception | `app/Exceptions/TicketLimitExceededException.php` | Thrown at 100% quota |
| Service | `app/Services/TicketService.php` | create, assign, updateStatus, addNote |
| Service | `app/Services/UsageCounterService.php` | + `getUsagePercent()` |
| Job | `app/Jobs/SendTicketStatusNotification.php` | Queued email on status change |
| Controller | `app/Http/Controllers/Engineer/TicketController.php` | index, show, assign, updateStatus, addNote |
| Controller | `app/Http/Controllers/Tenant/TicketController.php` | index, show, create/store, addNote |
| Controller | `app/Http/Controllers/Customer/TicketController.php` | create/store, index, show |

## Frontend

| File | Route | Who sees it |
|---|---|---|
| `Pages/Engineer/Tickets/Index.tsx` | `GET /engineer/tickets` | Engineer |
| `Pages/Engineer/Tickets/Show.tsx` | `GET /engineer/tickets/{ticket}` | Engineer |
| `Pages/Tenant/Tickets/Index.tsx` | `GET /portal/tickets` | Tenant admin |
| `Pages/Tenant/Tickets/Show.tsx` | `GET /portal/tickets/{ticket}` | Tenant admin |
| `Pages/Tenant/Tickets/Create.tsx` | `GET /portal/tickets/create` | Tenant admin |
| `Pages/Customer/Tickets/Create.tsx` | `GET /support/tickets/create` | Customer |
| `Pages/Customer/Tickets/Show.tsx` | `GET /support/tickets/{ticket}` | Customer |
| `layouts/AppLayout.tsx` | — | Shared layout |

## Routes summary

```
engineer.tickets.index    GET  /engineer/tickets
engineer.tickets.show     GET  /engineer/tickets/{ticket}
engineer.tickets.assign   POST /engineer/tickets/{ticket}/assign
engineer.tickets.status   PATCH /engineer/tickets/{ticket}/status
engineer.tickets.notes    POST /engineer/tickets/{ticket}/notes

tenant.tickets.index      GET  /portal/tickets
tenant.tickets.show       GET  /portal/tickets/{ticket}
tenant.tickets.create     GET  /portal/tickets/create
tenant.tickets.store      POST /portal/tickets
tenant.tickets.notes      POST /portal/tickets/{ticket}/notes

customer.tickets.index    GET  /support/tickets
customer.tickets.create   GET  /support/tickets/create
customer.tickets.store    POST /support/tickets
customer.tickets.show     GET  /support/tickets/{ticket}
```
