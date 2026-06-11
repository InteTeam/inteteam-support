<?php

declare(strict_types=1);

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\AgentAvailability;
use App\Models\ChatSession;
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

    public function queue(Request $request): Response
    {
        $queuedSessions = ChatSession::withoutGlobalScope('tenant')
            ->where('status', 'queued')
            ->with(['customer:id,name', 'tenant:id,name,slug'])
            ->latest()
            ->get();

        $mySessions = ChatSession::withoutGlobalScope('tenant')
            ->where('agent_id', $request->user()->id)
            ->where('status', 'active')
            ->with(['customer:id,name', 'tenant:id,name'])
            ->latest()
            ->get();

        $availability = AgentAvailability::where('user_id', $request->user()->id)->first();

        return Inertia::render('Engineer/Chat/Queue', [
            'queuedSessions' => $queuedSessions,
            'mySessions'     => $mySessions,
            'availability'   => $availability?->status ?? 'offline',
        ]);
    }

    public function accept(Request $request, ChatSession $session): RedirectResponse
    {
        $session = ChatSession::withoutGlobalScope('tenant')->findOrFail($session->id);

        $this->chat->acceptSession($session, $request->user());

        return redirect()->route('engineer.chat.show', $session->id)
            ->with('success', 'Session accepted.');
    }

    public function show(ChatSession $session): Response
    {
        $session = ChatSession::withoutGlobalScope('tenant')
            ->with(['customer:id,name', 'tenant:id,name', 'messages.author:id,name,role'])
            ->findOrFail($session->id);

        return Inertia::render('Engineer/Chat/Session', ['session' => $session]);
    }

    public function sendMessage(Request $request, ChatSession $session): JsonResponse
    {
        $session = ChatSession::withoutGlobalScope('tenant')->findOrFail($session->id);

        $data = $request->validate(['body' => 'required|string|min:1']);

        $message = $this->chat->sendMessage($session, $request->user(), $data['body']);

        return response()->json($message->load('author:id,name,role'));
    }

    public function close(ChatSession $session): RedirectResponse
    {
        $session = ChatSession::withoutGlobalScope('tenant')->findOrFail($session->id);

        $this->chat->closeSession($session);

        return redirect()->route('engineer.chat.queue')
            ->with('success', 'Session closed.');
    }

    public function convertToTicket(ChatSession $session): RedirectResponse
    {
        $session = ChatSession::withoutGlobalScope('tenant')->findOrFail($session->id);

        $ticket = $this->chat->convertToTicket($session);

        return redirect()->route('engineer.tickets.show', $ticket->id)
            ->with('success', 'Chat converted to ticket.');
    }

    public function setAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:online,away,offline']);

        $this->chat->setAvailability($request->user(), $data['status']);

        return back()->with('success', "Status set to {$data['status']}.");
    }
}
