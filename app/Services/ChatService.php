<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AgentAvailabilityChanged;
use App\Events\ChatMessageSent;
use App\Events\ChatSessionQueued;
use App\Exceptions\ChatAccessDeniedException;
use App\Exceptions\ChatLimitExceededException;
use App\Jobs\ConvertChatToTicket;
use App\Models\AgentAvailability;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(
        private readonly UsageCounterService $usage,
    ) {}

    /**
     * @return array{session: ChatSession, usage_warning: string|null}
     */
    public function startSession(Tenant $tenant, User $customer): array
    {
        // Group access gate
        $group = $customer->customerGroup;
        if (! $group || ! $group->hasFeature('chat')) {
            throw new ChatAccessDeniedException();
        }

        // Usage gate
        $percent = $this->usage->getUsagePercent($tenant, 'chat_sessions_per_month');
        if ($percent >= 100.0) {
            throw new ChatLimitExceededException();
        }

        // Block duplicate active/queued sessions
        $existing = ChatSession::withoutGlobalScope('tenant')
            ->where('end_customer_id', $customer->id)
            ->whereIn('status', ['queued', 'active'])
            ->first();

        if ($existing) {
            return ['session' => $existing, 'usage_warning' => null];
        }

        $session = ChatSession::create([
            'tenant_id'       => $tenant->id,
            'end_customer_id' => $customer->id,
            'status'          => 'queued',
        ]);

        $session->load('customer:id,name', 'tenant:id,name');

        broadcast(new ChatSessionQueued($session));

        ConvertChatToTicket::dispatch($session)->delay(now()->addSeconds(60));

        $warning = $percent >= 80.0
            ? sprintf('%.0f%% of monthly chat quota used.', $percent)
            : null;

        return ['session' => $session, 'usage_warning' => $warning];
    }

    public function acceptSession(ChatSession $session, User $engineer): ChatSession
    {
        return DB::transaction(function () use ($session, $engineer) {
            $fresh = ChatSession::withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->findOrFail($session->id);

            if ($fresh->status !== 'queued') {
                abort(409, 'Chat session is no longer available.');
            }

            $fresh->update([
                'status'   => 'active',
                'agent_id' => $engineer->id,
            ]);

            $this->usage->increment($fresh->tenant, 'chat_sessions_per_month');

            return $fresh->fresh();
        });
    }

    public function sendMessage(ChatSession $session, User $author, string $body): ChatMessage
    {
        if (! $session->isActive()) {
            abort(422, 'Cannot send messages in a session that is not active.');
        }

        $message = ChatMessage::create([
            'session_id' => $session->id,
            'author_id'  => $author->id,
            'body'       => $body,
            'sent_at'    => now(),
        ]);

        $message->load('author:id,name,role');

        broadcast(new ChatMessageSent($message))->toOthers();

        return $message;
    }

    public function closeSession(ChatSession $session): ChatSession
    {
        $session->update(['status' => 'closed']);

        return $session->fresh();
    }

    public function convertToTicket(ChatSession $session): Ticket
    {
        if (! in_array($session->status, ['queued', 'active'], true)) {
            abort(422, 'This session cannot be converted to a ticket.');
        }

        $messages = ChatMessage::where('session_id', $session->id)
            ->with('author:id,name')
            ->orderBy('sent_at')
            ->get();

        $transcript = $messages->isEmpty()
            ? 'No messages exchanged during this chat session.'
            : $messages->map(fn ($m) => "[{$m->author?->name}]: {$m->body}")->implode("\n");

        $ticket = Ticket::create([
            'tenant_id'       => $session->tenant_id,
            'end_customer_id' => $session->end_customer_id,
            'category'        => 'other',
            'description'     => "Converted from chat session #{$session->id}\n\n{$transcript}",
            'status'          => 'open',
        ]);

        $session->update([
            'status'    => 'converted_to_ticket',
            'ticket_id' => $ticket->id,
        ]);

        return $ticket;
    }

    public function setAvailability(User $engineer, string $status): AgentAvailability
    {
        $availability = AgentAvailability::updateOrCreate(
            ['user_id' => $engineer->id],
            ['status'  => $status],
        );

        broadcast(new AgentAvailabilityChanged($engineer->id, $status));

        return $availability;
    }
}
