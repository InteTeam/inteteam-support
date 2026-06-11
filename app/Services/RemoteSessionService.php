<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RemoteAccessDeniedException;
use App\Exceptions\RemoteLimitExceededException;
use App\Jobs\IncrementRemoteMinutes;
use App\Jobs\SendRemoteSessionRequest;
use App\Models\RemoteSession;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteSessionService
{
    public function __construct(
        private readonly UsageCounterService $usage,
    ) {}

    /**
     * Create a remote session request from a ticket.
     * Checks group feature gate + monthly minutes limit, then calls inteteam-remote
     * to obtain a signed session token.
     */
    public function requestSession(Ticket $ticket, User $engineer): array
    {
        $tenant   = $ticket->tenant;
        $customer = $ticket->customer;

        // Group feature gate
        $group = $customer->customerGroup;
        if ($group && ! ($group->features['remote'] ?? false)) {
            throw new RemoteAccessDeniedException();
        }

        // Usage gate
        $percent = $this->usage->getUsagePercent($tenant, 'remote_minutes_per_month');
        if ($percent >= 100.0) {
            throw new RemoteLimitExceededException();
        }

        $session = RemoteSession::create([
            'tenant_id'   => $tenant->id,
            'ticket_id'   => $ticket->id,
            'engineer_id' => $engineer->id,
            'customer_id' => $customer->id,
            'status'      => 'requested',
        ]);

        // Obtain session JWT from inteteam-remote signaling server
        $token = $this->fetchSignalingToken($session);
        $session->update(['session_token' => $token]);

        SendRemoteSessionRequest::dispatch($session);

        $warning = $percent >= 80.0
            ? sprintf('Warning: %.0f%% of your monthly remote minutes used.', $percent)
            : null;

        return ['session' => $session, 'usage_warning' => $warning];
    }

    public function acceptSession(RemoteSession $session, User $customer): void
    {
        abort_unless($session->customer_id === $customer->id, 403);
        abort_unless($session->isRequested(), 422, 'Session is no longer pending.');

        $session->update(['status' => 'accepted']);
    }

    public function declineSession(RemoteSession $session, User $customer): void
    {
        abort_unless($session->customer_id === $customer->id, 403);
        abort_unless($session->isRequested(), 422, 'Session is no longer pending.');

        $session->update(['status' => 'declined']);
    }

    public function markActive(RemoteSession $session): void
    {
        if ($session->isAccepted()) {
            $session->update(['status' => 'active', 'started_at' => now()]);
            // Schedule first minute increment
            IncrementRemoteMinutes::dispatch($session)->delay(60);
        }
    }

    public function endSession(RemoteSession $session): void
    {
        if ($session->isActive()) {
            $minutes = (int) ceil(now()->diffInMinutes($session->started_at));
            $session->update([
                'status'           => 'ended',
                'ended_at'         => now(),
                'duration_minutes' => $minutes,
            ]);
        }
    }

    public function logMinute(RemoteSession $session): void
    {
        if (! $session->isActive()) {
            return;
        }

        $this->usage->increment($session->tenant, 'remote_session_minutes');
        $session->increment('duration_minutes');

        // Re-schedule next minute unless session has ended
        IncrementRemoteMinutes::dispatch($session->fresh())->delay(60);
    }

    private function fetchSignalingToken(RemoteSession $session): string
    {
        $signalingUrl = rtrim(config('services.inteteam_remote.url', env('REMOTE_SIGNALING_URL', 'http://remote_signaling:8090')), '/');

        try {
            $response = Http::timeout(5)->post("{$signalingUrl}/api/sessions", [
                'session_id'  => $session->id,
                'tenant_id'   => $session->tenant_id,
                'engineer_id' => $session->engineer_id,
                'customer_id' => $session->customer_id,
                'ticket_id'   => $session->ticket_id,
            ]);

            if ($response->successful()) {
                return $response->json('token');
            }
        } catch (\Throwable $e) {
            Log::error('inteteam-remote token fetch failed', ['error' => $e->getMessage()]);
        }

        // Fallback: generate a placeholder token so the session row isn't broken.
        // The real session will fail at WebRTC handshake; engineer will see an error.
        return 'pending';
    }
}
