# Live Chat — Feature Overview

## User Stories

**End Customer**
- As a customer, I can open a live chat widget and be placed in a queue so I get real-time help.
- As a customer, I receive my messages in real-time once an engineer accepts my session.
- If no engineer answers within 60 seconds, my chat is automatically converted to a support ticket with the transcript attached.

**InteTeam Engineer**
- As an engineer, I can set my availability (online / away / offline).
- As an engineer, I see a live queue of waiting customers and can accept a session.
- Once I accept, only I (first-accept model) handle that session; it disappears from other engineers' queues.
- I can close a session or manually convert it to a ticket.

**Tenant Admin**
- As a tenant admin, I can enable or disable chat for each customer group.
- Customers in groups without chat enabled cannot start a chat session.

## Acceptance Criteria

### Session lifecycle
- [ ] Customer can start a session only if their group has `features.chat = true`.
- [ ] Starting a session when tenant is at monthly limit returns HTTP 422.
- [ ] Session created with status `queued`; `ChatSessionQueued` event broadcast to `chat-queue` channel.
- [ ] `ConvertChatToTicket` job dispatched with 60-second delay on session start.
- [ ] Engineer accepts via `POST /engineer/chat/{session}/accept`; session status → `active`.
- [ ] Acceptance is atomic (DB lock); second accept attempt returns 409.
- [ ] `chat_sessions_per_month` counter increments on accept.
- [ ] If no acceptance within 60 s, job converts session to a ticket with transcript.

### Messaging
- [ ] Messages broadcast in real-time over `chat-session.{id}` private channel.
- [ ] Both engineer and customer can send messages while session is `active`.

### Access control
- [ ] Guest redirected to login.
- [ ] Wrong role → 403.
- [ ] Customer cannot view another tenant's session (404 via `HasTenantScope`).

### Group configuration
- [ ] Tenant admin can toggle chat feature per group via `PATCH /portal/groups/{group}/features`.
