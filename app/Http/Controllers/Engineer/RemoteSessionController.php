<?php

declare(strict_types=1);

namespace App\Http\Controllers\Engineer;

use App\Exceptions\RemoteAccessDeniedException;
use App\Exceptions\RemoteLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\RemoteSession;
use App\Models\Ticket;
use App\Services\RemoteSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RemoteSessionController extends Controller
{
    public function __construct(
        private readonly RemoteSessionService $remote,
    ) {}

    /**
     * Request a new remote session for a ticket.
     * Only allowed on in_progress tickets.
     */
    public function request(Request $request, string $ticketId): RedirectResponse
    {
        $ticket = Ticket::withoutGlobalScope('tenant')->findOrFail($ticketId);

        if ($ticket->status !== 'in_progress') {
            return back()->withErrors(['remote' => 'Remote sessions can only be requested on in-progress tickets.']);
        }

        try {
            ['session' => $session, 'usage_warning' => $warning] = $this->remote->requestSession(
                $ticket,
                $request->user(),
            );
        } catch (RemoteAccessDeniedException $e) {
            return back()->withErrors(['remote' => $e->getMessage()]);
        } catch (RemoteLimitExceededException $e) {
            return back()->withErrors(['remote' => $e->getMessage()]);
        }

        $redirect = redirect()->route('engineer.remote.show', $session->id);
        if ($warning) {
            $redirect->with('usage_warning', $warning);
        }
        return $redirect;
    }

    /**
     * Show the stream viewer for an active/accepted session.
     */
    public function show(string $sessionId): Response
    {
        $session = RemoteSession::withoutGlobalScope('tenant')
            ->with(['ticket', 'customer', 'engineer', 'tenant'])
            ->findOrFail($sessionId);

        return Inertia::render('Engineer/Remote/Session', [
            'session'       => $session,
            'signalingHost' => config('services.inteteam_remote.ws_url', env('REMOTE_SIGNALING_WS_URL', 'wss://remote.inte.team')),
        ]);
    }

    /**
     * Engineer ends the session.
     */
    public function end(Request $request, string $sessionId): RedirectResponse
    {
        $session = RemoteSession::withoutGlobalScope('tenant')->findOrFail($sessionId);
        $this->remote->endSession($session);

        return redirect()->route('engineer.tickets.show', $session->ticket_id)
            ->with('success', 'Remote session ended.');
    }
}
