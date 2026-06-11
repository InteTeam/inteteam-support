<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

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

        // Tenant admins always get KB suggestions — they are creating tickets on behalf
        // of customers and benefit from self-service guidance regardless of group config.
        $articles = $this->kb->suggest($tenant, $validated['query']);

        return response()->json(['articles' => $articles]);
    }
}
