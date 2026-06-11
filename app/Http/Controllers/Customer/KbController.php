<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\KbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KbController extends Controller
{
    public function __construct(
        private readonly KbService $kb,
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $tenant = $request->attributes->get('tenant');
        $user   = $request->user();

        // Respect group feature gate — no query, no counter.
        $group = $user->customerGroup;
        if ($group === null || ! ($group->features['kb'] ?? false)) {
            return response()->json(['articles' => []]);
        }

        $articles = $this->kb->suggest($tenant, $validated['query']);

        return response()->json(['articles' => $articles]);
    }
}
