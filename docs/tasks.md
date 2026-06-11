# inteteam-support — Build Tasks

Read `docs/PRD.md` before starting any phase. Each phase must be fully complete and tested before the next begins.

---

## Phase 0 — Foundation ✅

- [x] Scaffold Laravel 12 + React 19 app (use oor-hq as template)
- [x] Docker: PHP-FPM + Nginx + PostgreSQL + Redis + queue-worker, `docker-compose.yml` + `docker-compose.prod.yml`. Container prefix: `support_`
- [x] SSO integration — all users authenticate via inteteam-sso (match CRM/oor-hq pattern); role `inteteam_staff` routes to engineer dashboard, `tenant_admin` routes to tenant portal, `end_customer` routes to customer view
- [x] Panel provisioning API endpoint — Panel can POST to create a tenant, assign tier, set usage limits (tickets_per_month, chat_sessions_per_month, remote_minutes_per_month, agents_allowed)
- [x] `tenants` migration + model — ULID PKs, plan limits JSON column, billing period tracking
- [x] `customer_groups` migration + model — belongs to tenant; name + features JSON (which channels are enabled per group)
- [x] `usage_counters` migration + model — tenant_id, metric (enum), period (YYYY-MM), count, last_reset_at; unique index on (tenant_id, metric, period)
- [x] `UsageCounterService` — increment(tenant, metric), reset(tenant, period), isWithinLimit(tenant, metric); wire to tenant plan limits
- [x] `install.sh` — must leave service fully running after single run; print all generated secrets with destinations; derive `DEPLOY_DIR` dynamically, never hardcode
- [x] `deploy.sh` — standard 12-step; first-time only
- [x] `post-deploy.sh` — called by Panel after every deploy: migrations, cache clear, queue restart, `append_if_missing` for all env keys
- [ ] Register app in Panel (auto-assigns port; use `${PORT}` in docker-compose host port)
- [x] `CLAUDE.md` + `docs/DATABASE_CONVENTIONS.md` + `docs/WORKFLOW_ENFORCEMENT.md` in repo root

---

## Phase 1 — Ticket System ✅

- [x] Feature doc: `docs/features/tickets/README.md` (user stories, acceptance criteria)
- [x] Architecture doc: `docs/features/tickets/architecture.md`
- [x] Component inventory: `docs/features/tickets/COMPONENT_INVENTORY.md`
- [x] `tickets` migration — ULID PK, tenant_id, end_customer_id, app, page, category (enum), description, status (enum: open/in_progress/resolved/closed), assigned_to, HasTenantScope trait
- [x] `ticket_notes` migration — ULID PK, ticket_id, author_id, body, is_internal (bool)
- [x] `Ticket` + `TicketNote` models with relationships and scopes
- [x] `TicketPolicy` — tenant sees own tickets only; engineer sees all
- [x] `TicketService` — create, assign, updateStatus, addNote
- [x] Engineer: `EngineerTicketController` — index (all tenants, filterable), show, assign, updateStatus, addNote
- [x] Tenant: `TenantTicketController` — index (own company), show, create
- [x] Customer: `CustomerTicketController` — create, show own tickets
- [x] Email notification on status change (queue job; Resend or SMTP per tenant setting)
- [x] Pages: engineer dashboard, ticket detail (engineer view), tenant portal, ticket detail (tenant view), new ticket form
- [ ] CRM widget: "Open Support" button passes SSO token + app + page context to inteteam-support
- [x] `tickets_per_month` counter increments in `TicketService::create`
- [x] Feature gate: block ticket creation when tenant is at monthly limit (warning at 80%, hard block at 100%)
- [x] Tests: guest redirected, wrong role → 403, ticket created (assertDatabaseHas), cross-tenant isolation → 404, status transitions, counter increments

---

## Phase 2 — Live Chat ✅

- [x] Feature doc + architecture doc + component inventory under `docs/features/chat/`
- [x] Install + configure Laravel Reverb (self-hosted WebSockets) — `config/reverb.php`, `config/broadcasting.php`, `support_reverb` container
- [x] `chat_sessions` migration — ULID PK, tenant_id, end_customer_id, agent_id (nullable), status (enum: queued/active/closed/converted_to_ticket), ticket_id (nullable FK for escalation)
- [x] `chat_messages` migration — ULID PK, session_id, author_id, body, sent_at
- [x] `ChatSession` + `ChatMessage` models (HasUlids, HasTenantScope)
- [x] `ChatService` — startSession, acceptSession, sendMessage, closeSession, convertToTicket, setAvailability
- [x] Agent availability: `agent_availability` table — toggle online/away/offline per engineer
- [x] Chat queue broadcast event (`ChatSessionQueued`) — all engineers see incoming requests on `chat-queue` channel
- [x] First-accept model — DB transaction + `lockForUpdate` prevents double-accept; 409 on race
- [x] Auto-ticket fallback — `ConvertChatToTicket` job dispatched with 60 s delay; converts if still queued
- [x] Pages: chat queue (`Engineer/Chat/Queue`), active chat window (`Engineer/Chat/Session`), customer chat widget (`Customer/Chat/Widget`)
- [x] Tenant configures which customer groups have chat access (`Tenant/Groups/Index` + `PATCH /portal/groups/{group}/features`)
- [x] `chat_sessions_per_month` counter increments on session accept
- [x] Feature gate: block new sessions when tenant is at monthly limit (80% warning, 100% block)
- [x] Tests: queue broadcast, auto-ticket fallback at 60s, cross-tenant isolation, counter increments, group access gate

---

## Phase 3 — Remote Desktop ✅

### inteteam-remote (Go repo — `InteTeam/inteteam-remote`)

- [x] Go module init (`go.mod`), add Pion WebRTC dependency
- [x] `CLAUDE.md` + `README.md` in repo root
- [x] Signaling server — WebSocket endpoint, session registry (session ID → peer pair), ICE candidate relay
- [x] TURN relay — Pion TURN embedded; restrict ICE to UDP `50000–50100`
- [x] Session auth — engineer creates session token via inteteam-support API; agent presents token to signaling server to join
- [x] Desktop agent (Windows 11) — screen capture via GDI (Desktop Duplication API path stubbed), mouse/keyboard injection via SendInput, headless background service, connects to `remote.inte.team` signaling server
- [x] Agent installer / silent install flag for distribution
- [x] `docker-compose.yml` for signaling server + TURN; NPM proxy config for `remote.inte.team`
- [x] `deploy.sh` for inteteam-remote on Dell R550

### inteteam-support integration

- [x] Feature doc + architecture doc under `docs/features/remote/`
- [x] `remote_sessions` migration — ULID PK, tenant_id, ticket_id (FK), engineer_id, customer_id, status (enum: requested/accepted/active/ended/declined), started_at, ended_at, duration_minutes
- [x] `RemoteSession` model + `RemoteSessionService` — requestSession, acceptSession, endSession, logDuration
- [x] Engineer: request session from ticket detail page; session token generated and sent to customer
- [x] Customer: in-app + email notification with accept/decline; agent download link if not installed
- [x] Stream UI — HTML5 canvas WebRTC consumer in engineer's browser, mouse/keyboard capture forwarded via DataChannel
- [x] Agent download page — Windows installer; shows install instructions
- [x] `remote_session_minutes` counter increments per minute via polling job during active session
- [x] `agents_registered` counter increments on first agent install per customer (agent calls registration endpoint)
- [x] Tenant can restrict remote to specific customer groups only
- [x] Feature gate: block session requests when tenant is at monthly minutes limit
- [x] Tests: session lifecycle, duration logging, counter increments, group access gate, cross-tenant isolation

---

## Phase 4 — Knowledge Base Surface ✅

- [x] Article suggestion in ticket creation flow — query `inteteam-rag` with ticket description, surface top 3 articles before submit ("Did you check this guide?")
- [x] Article suggestion in chat — surface relevant articles when session starts
- [x] `kb_lookups` counter increments on every RAG query from inteteam-support
- [x] Tenant can toggle KB suggestions on/off per customer group
