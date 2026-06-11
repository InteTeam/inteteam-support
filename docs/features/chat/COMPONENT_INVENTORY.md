# Live Chat — Component Inventory

## Backend

| Layer | File | Purpose |
|---|---|---|
| Migration | `…_add_customer_group_id_to_users_table.php` | `users.customer_group_id` FK |
| Migration | `…_create_agent_availability_table.php` | Per-engineer online/away/offline status |
| Migration | `…_create_chat_sessions_table.php` | Chat session lifecycle |
| Migration | `…_create_chat_messages_table.php` | Per-message storage |
| Model | `AgentAvailability` | Belongs to User; status enum |
| Model | `ChatSession` | HasUlids + HasTenantScope; status transitions |
| Model | `ChatMessage` | HasUlids; belongs to ChatSession |
| Event | `ChatSessionQueued` | Broadcasts to `chat-queue` when customer queues |
| Event | `ChatMessageSent` | Broadcasts to `chat-session.{id}` |
| Event | `AgentAvailabilityChanged` | Broadcasts to `agent-availability` |
| Job | `ConvertChatToTicket` | Runs 60 s after session start; converts if still queued |
| Exception | `ChatAccessDeniedException` | Group doesn't have chat feature |
| Exception | `ChatLimitExceededException` | Tenant at monthly chat limit |
| Service | `ChatService` | startSession, acceptSession, sendMessage, closeSession, convertToTicket, setAvailability |
| Controller | `Engineer/ChatController` | queue, accept, show, sendMessage, close, convertToTicket, setAvailability |
| Controller | `Customer/ChatController` | start, show, sendMessage |
| Controller | `Tenant/GroupController` | index, toggleFeature |
| Config | `config/reverb.php` | Reverb server config |
| Config | `config/broadcasting.php` | Reverb broadcast driver |

## Frontend

| File | Route | Who sees it |
|---|---|---|
| `Pages/Engineer/Chat/Queue.tsx` | `GET /engineer/chat` | Engineer — live queue |
| `Pages/Engineer/Chat/Session.tsx` | `GET /engineer/chat/{session}` | Engineer — active chat |
| `Pages/Customer/Chat/Widget.tsx` | `GET /support/chat` | Customer — chat widget |
| `Pages/Tenant/Groups/Index.tsx` | `GET /portal/groups` | Tenant admin — group config |
| `resources/js/bootstrap.ts` | — | Echo + axios init |

## Routes summary

```
engineer.chat.queue       GET  /engineer/chat
engineer.chat.accept      POST /engineer/chat/{session}/accept
engineer.chat.show        GET  /engineer/chat/{session}
engineer.chat.message     POST /engineer/chat/{session}/messages
engineer.chat.close       POST /engineer/chat/{session}/close
engineer.chat.convert     POST /engineer/chat/{session}/convert
engineer.chat.availability PATCH /engineer/chat/availability

customer.chat.show        GET  /support/chat
customer.chat.start       POST /support/chat
customer.chat.message     POST /support/chat/{session}/messages

tenant.groups.index       GET  /portal/groups
tenant.groups.feature     PATCH /portal/groups/{group}/features
```
