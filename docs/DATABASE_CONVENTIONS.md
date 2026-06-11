# Database Conventions

Follows `inte-playbook/laravel/DATABASE_CONVENTIONS.md`. PostgreSQL-specific additions below.

## Primary keys

All domain models use **ULID** primary keys via Laravel's `HasUlids` trait:

```php
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Tenant extends Model
{
    use HasUlids;
}
```

Base `users` table uses auto-increment `id` (standard Laravel — SSO users are looked up by email, not by ID from external systems).

## PostgreSQL specifics

- Use `$table->ulid('id')->primary()` in migrations (not `$table->id()` for domain models)
- Foreign keys to ULID tables: `$table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete()`
- JSON columns: `$table->json('plan_limits')->nullable()` — cast to `array` in model
- Enum-like columns: use `string` with app-level validation, not PostgreSQL ENUMs (easier to migrate)

## Tenant scoping

Domain models that belong to a tenant add `tenant_id` and a `HasTenantScope` trait (to be created in Phase 1, following CRM's `HasCompanyScope` pattern):

```php
use App\Models\Concerns\HasTenantScope;

class Ticket extends Model
{
    use HasUlids, HasTenantScope;
}
```

Cross-tenant isolation tests must expect **404** (not 403) — the scope causes `findOrFail` to throw `ModelNotFoundException` before the policy runs.

## Naming

- Tables: `snake_case` plural (`usage_counters`, `customer_groups`)
- Foreign keys: `{model}_id` (`tenant_id`, `customer_group_id`)
- Migration filenames: `YYYY_MM_DD_HHMMSS_create_{table}_table.php`
- Indexes: unique index on `(tenant_id, metric, period)` for `usage_counters`

## No raw SQL

Use Eloquent or Query Builder. Raw SQL only for complex aggregations with a comment explaining why.
