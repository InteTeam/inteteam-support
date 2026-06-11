<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

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
        $query = Ticket::where('end_customer_id', $request->user()->id)
            ->with(['assignee:id,name'])
            ->latest();

        return Inertia::render('Customer/Tickets/Index', [
            'tickets' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $kbEnabled = (bool) ($request->user()->customerGroup?->features['kb'] ?? false);

        return Inertia::render('Customer/Tickets/Create', [
            'usage' => [
                'percent' => $this->usage->getUsagePercent($tenant, 'tickets_per_month'),
            ],
            'context'   => $request->only('app', 'page'),
            'kbEnabled' => $kbEnabled,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        $redirect = redirect()->route('customer.tickets.show', $ticket);

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect->with('success', 'Your ticket has been submitted.');
    }

    public function show(Ticket $ticket, Request $request): Response
    {
        if ($ticket->end_customer_id !== $request->user()->id) {
            abort(404);
        }

        $ticket->load(['assignee:id,name', 'publicNotes.author:id,name']);

        return Inertia::render('Customer/Tickets/Show', ['ticket' => $ticket]);
    }
}
