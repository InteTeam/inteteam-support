<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CustomerOtpRelayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOtpController extends Controller
{
    public function __construct(
        private readonly CustomerOtpRelayService $relay,
    ) {}

    public function requestOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant' => ['required', 'string', 'max:63'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $tenant = Tenant::where('slug', $data['tenant'])->first();

        if ($tenant) {
            $this->relay->requestOtp($tenant, $data['email']);
        }

        // Always the same response regardless of whether the tenant/email
        // exist -- matches CRM's own anti-enumeration behaviour.
        return back()->with([
            'alert' => 'If an account exists, a verification code has been sent.',
            'type' => 'success',
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant' => ['required', 'string', 'max:63'],
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ]);

        $tenant = Tenant::where('slug', $data['tenant'])->first();

        if (! $tenant) {
            return back()->withErrors(['otp' => 'Invalid or expired code.']);
        }

        $customer = $this->relay->verifyOtp($tenant, $data['email'], $data['code']);

        if (! $customer) {
            return back()->withErrors(['otp' => 'Invalid or expired code.']);
        }

        $user = User::where('email', $customer['email'])->first();

        // `users.email` is globally unique across every role and tenant (schema
        // constraint, not something this controller can scope). If this email
        // already belongs to a user of a *different* tenant, don't silently
        // reassign tenant_id -- that would move their identity/history to the
        // wrong tenant. This is a real, unresolved product gap (can the same
        // person legitimately be a customer of two different repair shops with
        // one email?) -- reject clearly rather than guess. See
        // docs/features/customer-otp-login/README.md.
        if ($user && $user->tenant_id !== null && $user->tenant_id !== $tenant->id) {
            return back()->withErrors(['otp' => 'This email is already associated with a different account. Contact support.']);
        }

        if (! $user) {
            $user = User::create([
                'name' => $customer['name'] ?: $customer['email'],
                'email' => $customer['email'],
                'role' => 'end_customer',
                'tenant_id' => $tenant->id,
                'password' => '',
            ]);
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with(['alert' => 'Signed in.', 'type' => 'success']);
    }
}
