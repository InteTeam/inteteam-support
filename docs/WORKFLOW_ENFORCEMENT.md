# Workflow Enforcement

Follows `inte-playbook/workflow/README.md`.

## Before every commit

```bash
# Format
docker compose exec php-fpm ./vendor/bin/pint --dirty

# Static analysis
docker compose exec php-fpm ./vendor/bin/phpstan analyse

# Tests
docker compose exec php-fpm php artisan test
```

All three must pass. Never skip with `--no-verify`.

## Branch strategy

- `main` — production. Panel deploys from here.
- Feature branches: `feature/<slug>` (e.g. `feature/ticket-system`)
- Merge via PR. Squash merge for features, regular merge for releases.

## Phase gate

Each phase is complete only when:
1. All tasks in `docs/tasks.md` for that phase are checked
2. Tests cover: guest redirect, wrong role → 403, cross-tenant isolation → 404, counter increments
3. `post-deploy.sh` handles all new env keys via `append_if_missing`
4. `docs/tasks.md` phase header is marked `[x]`

## Code standards

- `declare(strict_types=1)` on every PHP file
- No inline authorization checks — use Policies
- No feature flags or backwards-compat shims — just change the code
- No comments explaining WHAT the code does — only WHY (non-obvious constraints/workarounds)
- Tests use factories — no fixtures
