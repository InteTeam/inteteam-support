# Customer Email-OTP Login — Feature Overview

**Status:** Planning
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
- [ ] Customer enters email on inteteam-support's login page
- [ ] inteteam-support calls CRM's `POST /api/v1/auth/customer/{company}/otp/request` (company resolved from the tenant context the customer arrived under — same `tenant_id` resolution inteteam-support already needs for the SSO paths)
- [ ] Always returns success regardless of whether the email exists (matches CRM's anti-enumeration behaviour — don't leak existence)

### Verify code
- [ ] Customer enters the 6-digit code
- [ ] inteteam-support calls CRM's `POST /api/v1/auth/customer/{company}/otp/verify`
- [ ] On success, CRM returns a customer JWT (`CustomerJwtService`) — inteteam-support exchanges/wraps this into its own session the same way `SsoController::findOrCreateUser()` does for SSO roles today, but keyed by the CRM `Customer` record, not an `inteteam_sso` `User`
- [ ] On failure (wrong code, expired, rate-limited), show a generic error — don't distinguish reasons to the customer

### Integration boundary
- [ ] `SsoController::callback()`'s `end_customer` branch is removed — it's unreachable via SSO claims and should not exist as dead code implying it works
- [ ] `sso_role` → role mapping in `SsoController::callback()` only ever produces `inteteam_staff` (root) or `tenant_admin` (company_admin **and** plain `user` — see cross-app plan decision #2) — never `end_customer`
- [ ] New `CustomerOtpController` (or similar) handles the two-step OTP flow independently of `SsoController`

### Tests
- [ ] Request code — always 200, rate limited after N attempts (mirror CRM's `RateLimiter` keys/limits, don't reinvent thresholds)
- [ ] Verify code — correct code logs in, wrong code fails, expired code fails, exceeded attempts locks out
- [ ] First-time customer — verify auto-creates local record, matches CRM's customer identity
- [ ] Cross-tenant isolation — a code requested under one company's context cannot verify against another company

---

## Open question carried from cross-app plan

None remaining for this feature specifically — all four cross-app decisions are settled (see `inte-playbook/architecture/sso-role-claims-and-support-auth.md` §5). Local admin/password login was decided against (SSO-only for staff/tenant_admin), so this doc's scope is customer OTP only.

---

## Not yet written

`architecture.md` and `COMPONENT_INVENTORY.md` for this feature — write those once the exact CRM-call boundary is settled (direct server-to-server call vs. a thin proxy) and before implementation starts, following the same trio pattern as `docs/features/tickets/`.
