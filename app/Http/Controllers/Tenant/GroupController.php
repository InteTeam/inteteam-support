<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $groups = CustomerGroup::where('tenant_id', $tenant->id)
            ->withCount('users')
            ->get();

        return Inertia::render('Tenant/Groups/Index', ['groups' => $groups]);
    }

    public function toggleFeature(Request $request, CustomerGroup $group): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        // Scoped to own tenant
        if ($group->tenant_id !== $tenant->id) {
            abort(404);
        }

        $data = $request->validate([
            'feature' => 'required|string|in:chat,remote',
            'enabled' => 'required|boolean',
        ]);

        $features = $group->features ?? [];
        $features[$data['feature']] = $data['enabled'];

        $group->update(['features' => $features]);

        return back()->with('success', "Feature '{$data['feature']}' updated.");
    }
}
