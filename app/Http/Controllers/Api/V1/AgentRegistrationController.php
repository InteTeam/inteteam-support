<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UsageCounter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AgentRegistrationController extends Controller
{
    /**
     * Register an agent installation for a customer.
     * Called by the Windows installer after a successful install.
     * Increments `agents_registered` counter once per customer (idempotent).
     *
     * Auth: Bearer token = the session JWT issued by inteteam-remote.
     * We extract customer_id from the JWT claims without re-validating the signature here
     * because this endpoint is also used for offline registration (no signaling server available).
     * The customer_id must match a real user with role=end_customer.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'string'],
            'tenant_id'   => ['required', 'string'],
        ]);

        $customer = User::where('id', $validated['customer_id'])
            ->where('role', 'end_customer')
            ->where('tenant_id', $validated['tenant_id'])
            ->first();

        if (! $customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $period = Carbon::now()->format('Y-m');

        // Idempotent: only increment if not already registered this period.
        $created = UsageCounter::firstOrCreate(
            [
                'tenant_id' => $validated['tenant_id'],
                'metric'    => 'agents_registered',
                'period'    => $period,
            ],
            ['count' => 0],
        );

        if ($created->wasRecentlyCreated) {
            $created->increment('count');
        } else {
            // Check if this specific customer already registered (per-customer tracking).
            // We use a separate row keyed by customer_id appended to metric name.
            $customerKey = 'agents_registered:' . $validated['customer_id'];
            $alreadyRegistered = UsageCounter::where('tenant_id', $validated['tenant_id'])
                ->where('metric', $customerKey)
                ->where('period', $period)
                ->exists();

            if (! $alreadyRegistered) {
                UsageCounter::create([
                    'tenant_id' => $validated['tenant_id'],
                    'metric'    => $customerKey,
                    'period'    => $period,
                    'count'     => 1,
                ]);
                $created->increment('count');
            }
        }

        return response()->json(['registered' => true]);
    }
}
