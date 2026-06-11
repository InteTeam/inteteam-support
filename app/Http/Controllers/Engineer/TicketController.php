<?php

declare(strict_types=1);

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
    ) {}

    public function index(Request $request): Response
    {
        $query = Ticket::withoutGlobalScope('tenant')
            ->with(['tenant:id,name,slug', 'customer:id,name,email', 'assignee:id,name'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tenantId = $request->query('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return Inertia::render('Engineer/Tickets/Index', [
            'tickets'  => $query->paginate(25)->withQueryString(),
            'tenants'  => Tenant::select('id', 'name', 'slug')->orderBy('name')->get(),
            'filters'  => $request->only('status', 'tenant_id', 'category'),
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load([
            'tenant:id,name,slug',
            'customer:id,name,email',
            'assignee:id,name',
            'notes.author:id,name,role',
        ]);

        return Inertia::render('Engineer/Tickets/Show', [
            'ticket'    => $ticket,
            'engineers' => User::where('role', 'engineer')->select('id', 'name')->get(),
        ]);
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $request->validate(['engineer_id' => 'required|exists:users,id']);

        $engineer = User::findOrFail($request->input('engineer_id'));
        $this->tickets->assign($ticket, $engineer);

        return back()->with('success', "Ticket assigned to {$engineer->name}.");
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('updateStatus', $ticket);

        $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);

        $this->tickets->updateStatus($ticket, $request->input('status'));

        return back()->with('success', 'Status updated.');
    }

    public function addNote(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('addNote', $ticket);

        $data = $request->validate([
            'body'        => 'required|string|min:1',
            'is_internal' => 'boolean',
        ]);

        $this->tickets->addNote($ticket, $request->user(), $data);

        return back()->with('success', 'Note added.');
    }
}
