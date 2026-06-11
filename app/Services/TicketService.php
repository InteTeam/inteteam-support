<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TicketLimitExceededException;
use App\Jobs\SendTicketStatusNotification;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;

class TicketService
{
    public function __construct(
        private readonly UsageCounterService $usage,
    ) {}

    /**
     * @param array{category: string, description: string, app?: string|null, page?: string|null} $data
     * @return array{ticket: Ticket, usage_warning: string|null}
     */
    public function create(Tenant $tenant, User $customer, array $data): array
    {
        $percent = $this->usage->getUsagePercent($tenant, 'tickets_per_month');

        if ($percent >= 100.0) {
            throw new TicketLimitExceededException();
        }

        $ticket = Ticket::create([
            'tenant_id'       => $tenant->id,
            'end_customer_id' => $customer->id,
            'category'        => $data['category'],
            'description'     => $data['description'],
            'app'             => $data['app'] ?? null,
            'page'            => $data['page'] ?? null,
            'status'          => 'open',
        ]);

        $this->usage->increment($tenant, 'tickets_per_month');

        $warning = $percent >= 80.0
            ? sprintf(
                'You have used %.0f%% of your monthly ticket quota (%d/%d).',
                $percent,
                $this->usage->getUsage($tenant, 'tickets_per_month'),
                $tenant->getLimit('tickets_per_month'),
            )
            : null;

        return ['ticket' => $ticket, 'usage_warning' => $warning];
    }

    public function assign(Ticket $ticket, User $engineer): Ticket
    {
        $ticket->update(['assigned_to' => $engineer->id]);

        return $ticket->fresh();
    }

    public function updateStatus(Ticket $ticket, string $newStatus): Ticket
    {
        if (! $ticket->canTransitionTo($newStatus)) {
            abort(422, "Cannot transition from '{$ticket->status}' to '{$newStatus}'.");
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $newStatus]);

        SendTicketStatusNotification::dispatch($ticket->fresh(), $oldStatus);

        return $ticket->fresh();
    }

    /**
     * @param array{body: string, is_internal?: bool} $data
     */
    public function addNote(Ticket $ticket, User $author, array $data): TicketNote
    {
        return TicketNote::create([
            'ticket_id'   => $ticket->id,
            'author_id'   => $author->id,
            'body'        => $data['body'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);
    }
}
