<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProvisioningController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug'                         => ['required', 'string', 'max:63', 'unique:tenants,slug'],
            'name'                         => ['required', 'string', 'max:255'],
            'tier'                         => ['required', Rule::in(['free', 'starter', 'pro'])],
            'plan_limits'                  => ['sometimes', 'array'],
            'plan_limits.tickets_per_month'        => ['sometimes', 'integer', 'min:0'],
            'plan_limits.chat_sessions_per_month'  => ['sometimes', 'integer', 'min:0'],
            'plan_limits.remote_minutes_per_month' => ['sometimes', 'integer', 'min:0'],
            'plan_limits.agents_allowed'           => ['sometimes', 'integer', 'min:0'],
        ]);

        $defaults = match ($validated['tier']) {
            'free'    => ['tickets_per_month' => 20,  'chat_sessions_per_month' => 0,   'remote_minutes_per_month' => 0,   'agents_allowed' => 0],
            'starter' => ['tickets_per_month' => 100, 'chat_sessions_per_month' => 50,  'remote_minutes_per_month' => 0,   'agents_allowed' => 1],
            'pro'     => ['tickets_per_month' => 0,   'chat_sessions_per_month' => 0,   'remote_minutes_per_month' => 300, 'agents_allowed' => 5],
            default   => [],
        };

        $planLimits = array_merge($defaults, $validated['plan_limits'] ?? []);

        $tenant = DB::transaction(function () use ($validated, $planLimits): Tenant {
            $tenant = Tenant::create([
                'slug'        => $validated['slug'],
                'name'        => $validated['name'],
                'tier'        => $validated['tier'],
                'plan_limits' => $planLimits,
            ]);

            // Default customer group
            CustomerGroup::create([
                'tenant_id' => $tenant->id,
                'name'      => 'Default',
                'features'  => ['tickets' => true, 'chat' => false, 'remote' => false],
            ]);

            return $tenant;
        });

        return response()->json([
            'id'   => $tenant->id,
            'slug' => $tenant->slug,
            'name' => $tenant->name,
        ], 201);
    }

    public function show(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        return response()->json([
            'id'          => $tenant->id,
            'slug'        => $tenant->slug,
            'name'        => $tenant->name,
            'tier'        => $tenant->tier,
            'plan_limits' => $tenant->plan_limits,
            'active'      => $tenant->active,
        ]);
    }

    public function suspend(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $tenant->update(['active' => false]);

        return response()->json(['status' => 'suspended']);
    }
}
