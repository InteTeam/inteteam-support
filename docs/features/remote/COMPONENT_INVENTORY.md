# Remote Desktop — Component Inventory

## Migrations

| File | Purpose |
|------|---------|
| `2026_06_11_000011_create_remote_sessions_table.php` | remote_sessions table |

## PHP

| File | Type | Purpose |
|------|------|---------|
| `app/Models/RemoteSession.php` | Model | HasUlids, HasTenantScope, status helpers |
| `app/Services/RemoteSessionService.php` | Service | requestSession, acceptSession, declineSession, endSession, logMinute |
| `app/Jobs/SendRemoteSessionRequest.php` | Job | Email + in-app notification to customer |
| `app/Jobs/IncrementRemoteMinutes.php` | Job | Increments counter once per minute for active sessions |
| `app/Http/Controllers/Engineer/RemoteSessionController.php` | Controller | request, show, end |
| `app/Http/Controllers/Customer/RemoteSessionController.php` | Controller | respond (accept/decline), agentDownload, agentReady |
| `app/Http/Controllers/Api/AgentRegistrationController.php` | Controller | register (increments agents_registered) |
| `app/Exceptions/RemoteLimitExceededException.php` | Exception | Thrown when remote_minutes limit hit |
| `app/Exceptions/RemoteAccessDeniedException.php` | Exception | Thrown when group lacks remote feature |

## React pages

| File | Purpose |
|------|---------|
| `resources/js/pages/Engineer/Remote/Session.tsx` | Stream viewer (canvas + WebRTC), polls status, sends control events |
| `resources/js/pages/Customer/Remote/AgentDownload.tsx` | Download agent + install instructions |
| `resources/js/pages/Customer/Remote/Waiting.tsx` | Shown after customer accepts + installs, waits for engineer |

## Routes

| Method | Path | Handler |
|--------|------|---------|
| POST | `/engineer/remote/{ticket}/request` | EngineerRemoteSessionController::request |
| GET | `/engineer/remote/{session}` | EngineerRemoteSessionController::show |
| POST | `/engineer/remote/{session}/end` | EngineerRemoteSessionController::end |
| GET | `/support/remote/{session}/respond` | CustomerRemoteSessionController::respond |
| GET | `/support/remote/agent` | CustomerRemoteSessionController::agentDownload |
| POST | `/support/remote/{session}/agent-ready` | CustomerRemoteSessionController::agentReady |
| POST | `/api/v1/agent/register` | AgentRegistrationController::register |
