<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePanelToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token    = $request->bearerToken() ?? $request->header('X-Panel-Token');
        $expected = config('services.panel.token');

        if (! $token || ! $expected || ! hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
