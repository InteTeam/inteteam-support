# Customer Email-OTP Login — Feature Overview

**Status:** ✅ COMPLETED 2026-08-28 — code, tests, quality gates landed. **Not deployable yet**: Panel provisioning doesn't populate `Tenant.sso_company_id`/`crm_company_slug` (new fields, out of scope here), so no live Tenant is actually linked to a CRM company yet. Architecture decisions and gaps found during implementation captured below.
**Cross-app plan:** `inte-playbook/architecture/sso-role-claims-and-support-auth.md` (read first)

---

## Why this exists

`SsoController::callback()` currently has an `end_customer` branch that expects an SSO OAuth claim of `role: end_customer` — but `inteteam_sso` has no concept of a tenant's end customers at all (it only models InteTeam staff and tenant company staff/admins — see cross-app plan §3). That branch can never fire. End customers (the people bringing devices in to a repair shop) need a login path that never goes through `inteteam_sso`.

`inteteam_crm` already has this built and live: `CustomerOtpService` + `Api/V1/CustomerAuthController` (`requestOtp`/`verifyOtp`, 6-digit code, 10-minute expiry, rate-limited, Resend-backed), already consumed by `store_front`'s `LoginForm.tsx`/`OtpInput.tsx`. This feature wires inteteam-support's customer login into that same service rather than building a parallel OTP system.

---

## User Stories

**End Customer**
- As a customer, I can request a login code sent to my email so I can access support without a password.
- As a customer, I can enter the 6-digit code to sign in.
- As a customer, I don't need an existing account — entering my email for the first time creates one (matches CRM's existing OTP behaviour: `verifyFirstTimeCustomer()` auto-creates the `Customer` record).

---

## Acceptance Criteria

### Request code
- [x] Customer enters email on inteteam-support's login page (`CustomerOtpForm` in `Auth/Login.tsx`, shown only when a `tenant` was resolved)
- [x] inteteam-support calls CRM's `POST /api/v1/auth/customer/{company}/otp/request` via `CustomerOtpRelayService` — `{company}` is `Tenant.crm_company_slug` (see "Architecture decisions" below, not the tenant context originally assumed)
- [x] Always returns success regardless of whether the tenant/email exists (matches CRM's anti-enumeration behaviour)

### Verify code
- [x] Customer enters the 6-digit code
- [x] inteteam-support calls CRM's `POST /api/v1/auth/customer/{company}/otp/verify`
- [x] On success, CRM returns `{data: {token, expires_in, customer: {...}}}` — inteteam-support reads only `data.customer` and creates/reuses its own local `User` (role `end_customer`), it does **not** store or forward CRM's customer JWT anywhere (inteteam-support has its own session, no need for CRM's token)
- [x] On failure (wrong code, expired, rate-limited, unlinked tenant), generic `"Invalid or expired code."` — no reason distinguished

### Integration boundary
- [x] `SsoController::callback()`'s `end_customer` branch removed
- [x] `sso_role` → role mapping produces `engineer` (root) or `tenant_admin` (company_admin **and** plain `user`) — never `end_customer`
- [x] New `CustomerOtpController` handles the two-step OTP flow independently of `SsoController`

### Tests
- [x] Request — always success regardless of tenant/email existing; relay call asserted via `Http::assertSent`
- [x] Verify — success logs in and creates/reuses the local customer; CRM rejection → generic error; cross-tenant email collision → rejected (see gap below, this is a deliberate safety choice, not full support)
- [ ] Rate limiting — routes have `throttle:5,1`/`throttle:10,1` but not exercised in tests (mirrors CRM's own limits, not independently verified end-to-end)

---

## Architecture decisions made during implementation (2026-08-28)

Two real gaps surfaced that weren't resolved by the cross-app plan and needed answers before any code could be written — both confirmed with Piotr:

1. **Tenant ↔ CRM company mapping.** `Tenant` had no field linking it to a CRM company at all — needed both for `tenant_admin` SSO login (`SsoController` was reading a `$claims['tenant_id']` that never existed) and for this feature's CRM relay call. Added two nullable columns: `Tenant.sso_company_id` (matches `inteteam_sso`'s Company ID, resolves `tenant_admin` SSO logins) and `Tenant.crm_company_slug` (CRM's `Company.slug`, used directly as the OTP API's `{company}` param). **Neither is populated by Panel's provisioning flow yet** — that's separate, later work, out of scope here. `ProvisioningController` accepts both as optional fields, ready for when Panel sends them.
2. **Customer tenant identification.** No way for an unauthenticated customer to be tenant-scoped before login (no subdomain-per-tenant, no session). Decided: `/login?tenant={slug}` query param, using inteteam-support's own `Tenant.slug` (not a CRM identifier). The still-unbuilt CRM "Open Support" widget (`docs/tasks.md` Phase 1, unchecked) is expected to link here with the slug.

## Known gap, deliberately not solved here

`users.email` is globally unique across the whole table (schema constraint, not scoped per tenant) — pre-existing, not introduced by this feature, but it means the same email cannot be an `end_customer` of two different tenants. `CustomerOtpController::verifyOtp()` rejects (rather than silently reassigns) when an email already belongs to a user of a *different* tenant. The previous `end_customer` SSO branch code (now removed) actually did silently reassign — that was almost certainly an unnoticed bug, not intended behaviour, since a silent tenant reassignment could move a customer's ticket history to the wrong tenant. Whether the product actually needs multi-tenant email support is an open question for whoever owns customer identity design next.

## Not yet written

`architecture.md` and `COMPONENT_INVENTORY.md` — the implementation above is small enough (2 controllers, 1 service, no new component library) that the README's "Architecture decisions" section above covers it; write the separate files if this feature grows (e.g. adds password/2FA options for customers).
