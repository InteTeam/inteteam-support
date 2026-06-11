<?php

use App\Models\ChatSession;
use Illuminate\Support\Facades\Broadcast;

// Default user model channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// All engineers see the queue
Broadcast::channel('chat-queue', function ($user) {
    return $user->isEngineer();
});

// Engineer + customer of a specific session
Broadcast::channel('chat-session.{sessionId}', function ($user, $sessionId) {
    $session = ChatSession::withoutGlobalScope('tenant')->find($sessionId);

    if (! $session) {
        return false;
    }

    return $user->id === $session->agent_id
        || $user->id === $session->end_customer_id
        || $user->isEngineer();
});

// Engineers receive availability updates
Broadcast::channel('agent-availability', function ($user) {
    return $user->isEngineer();
});
