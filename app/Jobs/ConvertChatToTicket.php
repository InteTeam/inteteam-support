<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertChatToTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly ChatSession $session,
    ) {}

    public function handle(): void
    {
        // Re-fetch with lock to avoid race with engineer accepting
        $session = ChatSession::withoutGlobalScope('tenant')
            ->find($this->session->id);

        if (! $session || $session->status !== 'queued') {
            return;
        }

        $messages = ChatMessage::where('session_id', $session->id)
            ->with('author:id,name')
            ->orderBy('sent_at')
            ->get();

        $transcript = $messages->isEmpty()
            ? 'Customer requested chat but no agent accepted within 60 seconds.'
            : $messages->map(fn ($m) => "[{$m->author?->name}]: {$m->body}")->implode("\n");

        $ticket = Ticket::create([
            'tenant_id'       => $session->tenant_id,
            'end_customer_id' => $session->end_customer_id,
            'category'        => 'other',
            'description'     => "Auto-converted from chat session #{$session->id}\n\n{$transcript}",
            'status'          => 'open',
        ]);

        $session->update([
            'status'    => 'converted_to_ticket',
            'ticket_id' => $ticket->id,
        ]);
    }
}
