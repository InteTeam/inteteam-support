<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exceptions\TicketLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Services\UsageCounterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly UsageCounterService $usage,
    ) {}

    public function index(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $query = Ticket::with(['customer:id,name,email', 'assignee:id,name'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return Inertia::render('Tenant/Tickets/Index', [
            'tickets' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only('status', 'category'),
            'usage'   => [
                'count'   => $this->usage->getUsage($tenant, 'tickets_per_month'),
                'limit'   => $tenant->getLimit('tickets_per_month'),
                'percent' => $this->usage->getUsagePercent($tenant, 'tickets_per_month'),
            ],
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load([
            'customer:id,name,email',
            'assignee:id,name',
            'publicNotes.author:id,name,role',
        ]);

        return Inertia::render('Tenant/Tickets/Show', ['ticket' => $ticket]);
    }

    public function create(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        return Inertia::render('Tenant/Tickets/Create', [
            'usage' => [
                'count'   => $this->usage->getUsage($tenant, 'tickets_per_month'),
                'limit'   => $tenant->getLimit('tickets_per_month'),
                'percent' => $this->usage->getUsagePercent($tenant, 'tickets_per_month'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $data = $request->validate([
            'category'    => 'required|in:hardware,software,billing,other',
            'description' => 'required|string|min:10',
            'app'         => 'nullable|string|max:100',
            'page'        => 'nullable|string|max:255',
        ]);

        try {
            ['ticket' => $ticket, 'usage_warning' => $warning] =
                $this->tickets->create($tenant, $request->user(), $data);
        } catch (TicketLimitExceededException $e) {
            return back()->withErrors(['limit' => $e->getMessage()]);
        }

        $redirect = redirect()->route('tenant.tickets.show', $ticket);

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect->with('success', 'Ticket created.');
    }

    public function addNote(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('addNote', $ticket);

        $data = $request->validate(['body' => 'required|string|min:1']);

        $this->tickets->addNote($ticket, $request->user(), ['body' => $data['body'], 'is_internal' => false]);

        return back()->with('success', 'Note added.');
    }
}
