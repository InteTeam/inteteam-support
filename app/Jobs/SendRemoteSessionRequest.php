<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RemoteSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRemoteSessionRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly RemoteSession $session,
    ) {}

    public function handle(): void
    {
        $session  = $this->session->load(['customer', 'engineer', 'ticket', 'tenant']);
        $customer = $session->customer;

        $acceptUrl  = route('customer.remote.respond', ['session' => $session->id, 'action' => 'accept']);
        $declineUrl = route('customer.remote.respond', ['session' => $session->id, 'action' => 'decline']);

        $body = implode("\n\n", [
            "Hello {$customer->name},",
            "{$session->engineer->name} from {$session->tenant->name} has requested a remote desktop session to help with your support ticket.",
            "Ticket: {$session->ticket->description}",
            "To ACCEPT the request and allow screen sharing, click:",
            $acceptUrl,
            "To DECLINE, click:",
            $declineUrl,
            "This link expires in 24 hours. If you did not expect this request, you can safely decline.",
            "InteTeam Support",
        ]);

        Mail::raw($body, function ($message) use ($customer, $session) {
            $message
                ->to($customer->email)
                ->subject("[{$session->tenant->name}] Remote Desktop Session Request");
        });
    }
}
