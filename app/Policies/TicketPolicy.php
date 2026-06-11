<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isEngineer()) {
            return true;
        }

        if ($user->isTenantAdmin()) {
            return $user->tenant_id === $ticket->tenant_id
                || session('current_tenant_id') === $ticket->tenant_id;
        }

        return $user->id === $ticket->end_customer_id;
    }

    public function create(User $user): bool
    {
        return $user->isTenantAdmin() || $user->isEndCustomer();
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->isEngineer()
            || ($user->isTenantAdmin() && $this->view($user, $ticket));
    }

    public function assign(User $user): bool
    {
        return $user->isEngineer();
    }

    public function addNote(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function addInternalNote(User $user): bool
    {
        return $user->isEngineer();
    }
}
