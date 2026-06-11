<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\RemoteSession;
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
     * Accept or decline a remote session request.
     * Accessed via link in email / in-app notification:
     * GET /support/remote/{session}/respond?action=accept|decline
     */
    public function respond(Request $request, string $sessionId): RedirectResponse|Response
    {
        // Use withoutGlobalScope because the customer reads tenant from their own user record,
        // which is already validated by the 'tenant' middleware, but the session's HasTenantScope
        // may filter it out when tenant is resolved from session attribute vs user attribute.
        $session = RemoteSession::withoutGlobalScope('tenant')
            ->with(['ticket', 'engineer'])
            ->findOrFail($sessionId);

        // Ensure this customer owns the session.
        if ($session->customer_id !== $request->user()->id) {
            abort(404);
        }

        $action = $request->query('action');

        if ($action === 'decline') {
            $this->remote->declineSession($session, $request->user());
            return redirect()->route('customer.tickets.show', $session->ticket_id)
                ->with('info', 'Remote session declined.');
        }

        if ($action === 'accept') {
            $this->remote->acceptSession($session, $request->user());
            return redirect()->route('customer.remote.agent', ['session' => $session->id]);
        }

        // No action — show the accept/decline prompt page.
        return Inertia::render('Customer/Remote/RespondPage', [
            'session'     => $session->only('id', 'created_at'),
            'engineer'    => $session->engineer->only('name'),
            'ticket'      => $session->ticket->only('id', 'description'),
            'acceptUrl'   => route('customer.remote.respond', ['session' => $sessionId, 'action' => 'accept']),
            'declineUrl'  => route('customer.remote.respond', ['session' => $sessionId, 'action' => 'decline']),
        ]);
    }

    /**
     * Agent download / "launch agent" page.
     * Shown after customer accepts. If agent is already installed, displays instructions
     * to launch it. If not installed, shows download link + install steps.
     */
    public function agentDownload(Request $request): Response
    {
        $sessionId = $request->query('session');
        $session   = null;

        if ($sessionId) {
            $session = RemoteSession::withoutGlobalScope('tenant')
                ->select('id', 'session_token', 'status', 'customer_id')
                ->find($sessionId);

            if ($session && $session->customer_id !== $request->user()->id) {
                abort(404);
            }
        }

        return Inertia::render('Customer/Remote/AgentDownload', [
            'session'     => $session ? $session->only('id', 'session_token', 'status') : null,
            'downloadUrl' => route('customer.remote.agent.download-file'),
        ]);
    }

    /**
     * Serve the agent binary download.
     */
    public function downloadFile(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = storage_path('app/agent/agent.exe');

        if (! file_exists($path)) {
            abort(404, 'Agent binary not available yet. Please contact support.');
        }

        return response()->download($path, 'InteTeamRemoteAgent.exe');
    }

    /**
     * Customer signals that the agent is installed and ready.
     * Sets session status to accepted (if still requested) — triggers engineer's polling to detect.
     */
    public function agentReady(Request $request, string $sessionId): RedirectResponse
    {
        $session = RemoteSession::withoutGlobalScope('tenant')->findOrFail($sessionId);

        if ($session->customer_id !== $request->user()->id) {
            abort(404);
        }

        $this->remote->markActive($session);

        return redirect()->route('customer.remote.waiting', ['session' => $sessionId]);
    }

    /**
     * Waiting page — shown while engineer's browser connects.
     */
    public function waiting(Request $request, string $sessionId): Response
    {
        $session = RemoteSession::withoutGlobalScope('tenant')
            ->select('id', 'status', 'customer_id')
            ->findOrFail($sessionId);

        if ($session->customer_id !== $request->user()->id) {
            abort(404);
        }

        return Inertia::render('Customer/Remote/Waiting', [
            'session' => $session->only('id', 'status'),
        ]);
    }
}
