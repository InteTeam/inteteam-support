<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Exceptions\ChatAccessDeniedException;
use App\Exceptions\ChatLimitExceededException;
use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\Tenant;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chat,
    ) {}

    public function show(Request $request): Response
    {
        $activeSession = ChatSession::withoutGlobalScope('tenant')
            ->where('end_customer_id', $request->user()->id)
            ->whereIn('status', ['queued', 'active'])
            ->with(['messages.author:id,name,role', 'agent:id,name'])
            ->latest()
            ->first();

        $kbEnabled = (bool) ($request->user()->customerGroup?->features['kb'] ?? false);

        return Inertia::render('Customer/Chat/Widget', [
            'activeSession' => $activeSession,
            'kbEnabled'     => $kbEnabled,
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        try {
            ['session' => $session, 'usage_warning' => $warning] =
                $this->chat->startSession($tenant, $request->user());
        } catch (ChatAccessDeniedException $e) {
            return back()->withErrors(['chat' => $e->getMessage()]);
        } catch (ChatLimitExceededException $e) {
            return back()->withErrors(['chat' => $e->getMessage()]);
        }

        $redirect = redirect()->route('customer.chat.show');

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function sendMessage(Request $request, ChatSession $session): JsonResponse
    {
        // Verify this customer owns the session
        if ($session->end_customer_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate(['body' => 'required|string|min:1']);

        $message = $this->chat->sendMessage($session, $request->user(), $data['body']);

        return response()->json($message->load('author:id,name,role'));
    }
}
