# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Session setup

```bash
git config user.name "piotrfx" && git config user.email "shopscot@gmail.com"
```

## Project overview

InteTeam Support is a multi-tenant support platform for repair shops. It sits between customers/tenants and the InteTeam engineering team, providing tickets (Phase 1), live chat (Phase 2), remote desktop (Phase 3), and AI-powered KB search (Phase 4).

Stack: **Laravel 12 / PHP 8.4 + React 19 / TypeScript / Inertia.js + PostgreSQL 16 + Redis 7**

## Development commands

```bash
# Start dev environment (PostgreSQL, Redis, PHP-FPM, Nginx, Vite HMR)
docker compose --profile dev up -d

# Run tests
docker compose exec php-fpm php artisan test

# Code quality
docker compose exec php-fpm ./vendor/bin/pint --dirty
docker compose exec php-fpm ./vendor/bin/phpstan analyse

# Migrations
docker compose exec php-fpm php artisan migrate

# Frontend
docker compose run --rm npm run build
docker compose run --rm npm run dev
```

Dev URLs: http://localhost (app), http://localhost:5173 (Vite HMR)

## Architecture

### User roles and routing

SSO login is currently broken platform-wide (`sso_role` never reaches this app's claims) — see
`docs/features/customer-otp-login/README.md` and `inte-playbook/architecture/sso-role-claims-and-support-auth.md`.

| Role | Auth method | Redirects to |
|---|---|---|
| `inteteam_staff` | SSO only | `/engineer/dashboard` |
| `tenant_admin` | SSO only | `/portal/dashboard` |
| `end_customer` | Email-OTP only (never SSO — `inteteam_sso` has no end-customer concept) | `/support/dashboard` |

### Multi-tenancy

Tenants are isolated by `tenant_id` FK — no global scope yet (unlike CRM's `HasCompanyScope`). Add a `HasTenantScope` trait in Phase 1 following the same pattern.

### Key models

| Model | Purpose |
|---|---|
| `Tenant` | ULID PK, plan limits JSON, tier |
| `CustomerGroup` | Belongs to tenant; per-group feature flags |
| `UsageCounter` | tenant_id + metric + period → count; unique index |

### Key service

`UsageCounterService` — `increment(tenant, metric)`, `isWithinLimit(tenant, metric)`, `reset(tenant, period)`. Every Phase 1–4 feature that consumes a monthly quota calls `increment` and checks `isWithinLimit` before creating the resource.

### Panel provisioning API

`POST /api/v1/provisioning/tenants` — authenticated with `PANEL_TOKEN` (Bearer or `X-Panel-Token` header). Creates tenant + default CustomerGroup atomically.

### Deployment

- `install.sh` — first-time deploy (12 steps, idempotent)
- `deploy.sh` — thin alias for install.sh (for Panel first-deploy hook)
- `post-deploy.sh` — called by Panel after every git pull; handles migrations + cache + frontend rebuild

## Code conventions

- `declare(strict_types=1)` on every PHP file
- ULID primary keys (`HasUlids` trait)
- PHP 8 constructor property promotion
- Explicit return types on all methods
- Tests expect **404** (not 403) for cross-tenant isolation — matches `HasTenantScope` behaviour
- DB: PostgreSQL only (no MariaDB)
- Container prefix: `support_`
- Port from `${PORT}` env — never hardcode

## Read first

- `docs/CONTEXT.md` — infrastructure, team, constraints
- `docs/PRD.md` — product requirements for all phases
- `docs/tasks.md` — phase-by-phase task checklist
