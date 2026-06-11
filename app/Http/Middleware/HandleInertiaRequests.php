<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ] : null,
                'tenant' => $request->session()->get('current_tenant_id')
                    ? ['id' => $request->session()->get('current_tenant_id')]
                    : null,
            ],
            'flash' => fn () => [
                'alert'  => $request->session()->get('flash.alert'),
                'type'   => $request->session()->get('flash.type'),
                'status' => $request->session()->get('status'),
            ],
        ]);
    }
}
