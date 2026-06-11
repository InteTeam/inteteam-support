# Remote Desktop — Architecture

## Data model

```
remote_sessions
  id              ULID PK
  tenant_id       FK → tenants
  ticket_id       FK → tickets
  engineer_id     FK → users (role=engineer)
  customer_id     FK → users (role=end_customer)
  session_token   text nullable  -- JWT stored after signaling server issues it
  status          enum(requested,accepted,active,ended,declined)
  started_at      timestamp nullable  -- set when status transitions to active
  ended_at        timestamp nullable  -- set when status transitions to ended
  duration_minutes int default 0
  created_at / updated_at
```

## Service layer

`RemoteSessionService`
- `requestSession(Ticket, User $engineer): RemoteSession`
  - Feature gate: `isWithinLimit(tenant, 'remote_minutes_per_month')`
  - Group gate: `$customer->customerGroup->features['remote'] ?? false`
  - Creates row, calls inteteam-remote API to get `session_token`, dispatches `SendRemoteSessionRequest` job
- `acceptSession(RemoteSession, User $customer): void`
  - Validates customer owns the session, status=requested
  - Sets `status=accepted`
- `declineSession(RemoteSession, User $customer): void`
- `markActive(RemoteSession): void` — called by polling job when agent connects
- `endSession(RemoteSession): void` — sets `ended_at`, computes `duration_minutes`
- `logMinute(RemoteSession): void` — increments `remote_session_minutes` counter

## Broadcast / real-time

No Reverb events for Phase 3 — the WebRTC stream is fully peer-to-peer via inteteam-remote.
inteteam-support only manages session lifecycle state. Engineer browser polls `/engineer/remote/{session}/status`
(Inertia `router.reload`) to detect when status transitions to `active`.

## Feature gate locations

1. `RemoteSessionService::requestSession` — hard block at 100%, warning at 80%
2. `CustomerGroup::features['remote']` — checked before creating request
3. Route middleware: customer `/support/remote/*` routes reject if group has no remote feature

## Agent registration

`POST /api/v1/agent/register` (public, rate-limited, Bearer token = installer JWT)
- Increments `agents_registered` counter once per customer (idempotent via `firstOrCreate` on `usage_counters`)
- Returns 200 OK

## Component map

```
Engineer flow:
  Engineer/Tickets/Show.tsx
    → "Request Remote Session" button (only when ticket status=in_progress)
    → POST /engineer/remote/{ticket}/request
    → RemoteSessionController::request
    → RemoteSessionService::requestSession
    → Redirects to Engineer/Remote/Session.tsx (polling for active)

  Engineer/Remote/Session.tsx
    → WebRTC peer connection to wss://remote.inte.team/ws/engineer?token=...
    → Canvas renders incoming JPEG frames from DataChannel
    → mousemove/click/keydown events → DataChannel → agent

Customer flow:
  In-app notification + email link → GET /support/remote/{session}/respond?action=accept|decline
  → RemoteSessionController::respond (customer)
  → If accepted: redirect to /support/remote/agent (download page or auto-launch)

Agent download page:
  Customer/Remote/AgentDownload.tsx
    → Download link for agent.exe + install.ps1
    → Install instructions
    → "I've installed the agent" button → POST /support/remote/{session}/agent-ready
    → Customer/Remote/Waiting.tsx while engineer's browser connects
```
