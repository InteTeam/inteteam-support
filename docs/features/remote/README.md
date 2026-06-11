# Remote Desktop — Feature Documentation

## User stories

| As a… | I want to… | So that… |
|---|---|---|
| InteTeam engineer | Request a remote desktop session from within a ticket | I can diagnose hardware issues without leaving the support platform |
| End customer | Accept or decline a remote session request | I maintain control over when my machine is accessed |
| End customer | Download the InteTeam desktop agent once | Future sessions are one-click without reinstalling |
| Tenant admin | Restrict remote desktop to specific customer groups | Premium customers get remote access; basic tier does not |
| InteTeam engineer | See the customer's screen in my browser + control mouse/keyboard | I can fix hardware issues (printer drivers, scanner config) remotely |

## Acceptance criteria

- [ ] Engineer initiates a session from the ticket detail page (button visible only on in_progress tickets)
- [ ] Customer receives an in-app notification AND email with an accept/decline link
- [ ] Explicit customer accept is required before the session starts
- [ ] Desktop agent is downloadable from `/support/remote/agent/download`
- [ ] Agent is a headless Windows Service — no tray icon, no user interaction during session
- [ ] Engineer's browser renders the remote screen via WebRTC (HTML5 canvas)
- [ ] Engineer can send mouse and keyboard events via WebRTC DataChannel
- [ ] Session is logged: start time, end time, engineer, tenant, customer, ticket link
- [ ] `remote_session_minutes` counter increments per minute of active session
- [ ] `agents_registered` counter increments on first agent install per customer
- [ ] Tenant can restrict remote to specific customer groups (feature flag: `remote`)
- [ ] Feature gate: block session requests when tenant is at monthly minutes limit (80% warning, 100% hard block)

## Status flow

```
requested → accepted → active → ended
         ↘ declined
```

- `requested` — engineer clicked "Request Session"; customer has not yet responded
- `accepted` — customer accepted; agent is connecting to signaling server
- `active` — both peers connected; screen is streaming
- `ended` — session closed by either party
- `declined` — customer declined the request

## Session lifecycle (happy path)

1. Engineer clicks "Request Remote Session" on ticket detail page
2. `RemoteSessionService::requestSession()` creates a `remote_sessions` row with `status=requested`
3. inteteam-support calls `POST https://remote.inte.team/api/sessions` → receives JWT `session_token`
4. Token stored on `remote_sessions.session_token`
5. `SendRemoteSessionRequest` job dispatched: sends in-app notification + email to customer with accept/decline links
6. Customer clicks "Accept" → `RemoteSessionService::acceptSession()` sets `status=accepted`
7. Customer's browser redirects to agent download page if agent not installed, or auto-launches with `inteteam://session?token=...`
8. Agent connects to `wss://remote.inte.team/ws/agent?token=...`
9. Engineer's browser opens stream page, connects to `wss://remote.inte.team/ws/engineer?token=...`
10. Signaling server relays SDP + ICE → WebRTC P2P established → `status=active`
11. `IncrementRemoteMinutes` job polls every 60 s → increments `remote_session_minutes` counter
12. Either party closes → `RemoteSessionService::endSession()` → `status=ended`

## Infrastructure

- Signaling server + TURN relay: `inteteam-remote` on Dell R550 at `remote.inte.team`
- NPM reverse proxies `remote.inte.team` → Docker container port 8090
- UDP 50000–50100 forwarded directly (TURN relay media — cannot go through NPM)
- JWT_SECRET shared between inteteam-support and inteteam-remote
