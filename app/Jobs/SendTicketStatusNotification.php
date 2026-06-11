<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $previousStatus,
    ) {}

    public function handle(): void
    {
        $customer = $ticket->customer ?? User::find($this->ticket->end_customer_id);

        if (! $customer) {
            return;
        }

        $subject = sprintf(
            '[Support] Ticket #%s status changed to %s',
            substr($this->ticket->id, -8),
            strtoupper($this->ticket->status),
        );

        $body = implode("\n", [
            "Hello {$customer->name},",
            '',
            "Your support ticket status has been updated.",
            '',
            "Ticket: #{$this->ticket->id}",
            "Category: {$this->ticket->category}",
            "Previous status: {$this->previousStatus}",
            "New status: {$this->ticket->status}",
            '',
            "Description:",
            $this->ticket->description,
        ]);

        Mail::raw($body, function ($message) use ($customer, $subject) {
            $message
                ->to($customer->email, $customer->name)
                ->subject($subject);
        });
    }
}
