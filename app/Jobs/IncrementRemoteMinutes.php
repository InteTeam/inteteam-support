<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RemoteSession;
use App\Services\RemoteSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncrementRemoteMinutes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly RemoteSession $session,
    ) {}

    public function handle(RemoteSessionService $remote): void
    {
        // Re-fetch to get the latest status — session may have ended since dispatch.
        $session = RemoteSession::withoutGlobalScope('tenant')->find($this->session->id);

        if ($session === null || ! $session->isActive()) {
            return;
        }

        $remote->logMinute($session);
    }
}
