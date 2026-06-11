<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\Customer\ChatController as CustomerChatController;
use App\Http\Controllers\Customer\KbController as CustomerKbController;
use App\Http\Controllers\Customer\RemoteSessionController as CustomerRemoteController;
use App\Http\Controllers\Customer\TicketController as CustomerTicketController;
use App\Http\Controllers\Engineer\ChatController as EngineerChatController;
use App\Http\Controllers\Engineer\DashboardController as EngineerDashboard;
use App\Http\Controllers\Engineer\RemoteSessionController as EngineerRemoteController;
use App\Http\Controllers\Engineer\TicketController as EngineerTicketController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\GroupController as TenantGroupController;
use App\Http\Controllers\Tenant\KbController as TenantKbController;
use App\Http\Controllers\Tenant\TicketController as TenantTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('auth.sso.redirect');
Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('auth.sso.callback');

/*
|--------------------------------------------------------------------------
| Engineer (inteteam_staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:engineer'])
    ->prefix('engineer')
    ->name('engineer.')
    ->group(function () {
        Route::get('/dashboard', [EngineerDashboard::class, 'index'])->name('dashboard');

        // Chat
        Route::get('/chat', [EngineerChatController::class, 'queue'])->name('chat.queue');
        Route::post('/chat/availability', [EngineerChatController::class, 'setAvailability'])->name('chat.availability');
        Route::post('/chat/{session}/accept', [EngineerChatController::class, 'accept'])->name('chat.accept');
        Route::get('/chat/{session}', [EngineerChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/{session}/messages', [EngineerChatController::class, 'sendMessage'])->name('chat.message');
        Route::post('/chat/{session}/close', [EngineerChatController::class, 'close'])->name('chat.close');
        Route::post('/chat/{session}/convert', [EngineerChatController::class, 'convertToTicket'])->name('chat.convert');

        // Remote sessions
        Route::post('/remote/{ticket}/request', [EngineerRemoteController::class, 'request'])->name('remote.request');
        Route::get('/remote/{session}', [EngineerRemoteController::class, 'show'])->name('remote.show');
        Route::post('/remote/{session}/end', [EngineerRemoteController::class, 'end'])->name('remote.end');

        Route::get('/tickets', [EngineerTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [EngineerTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/assign', [EngineerTicketController::class, 'assign'])->name('tickets.assign');
        Route::patch('/tickets/{ticket}/status', [EngineerTicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/tickets/{ticket}/notes', [EngineerTicketController::class, 'addNote'])->name('tickets.notes');
    });

/*
|--------------------------------------------------------------------------
| Tenant portal (tenant_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:tenant_admin', 'tenant'])
    ->prefix('portal')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', [TenantDashboard::class, 'index'])->name('dashboard');

        // Groups / features
        Route::get('/groups', [TenantGroupController::class, 'index'])->name('groups.index');
        Route::patch('/groups/{group}/features', [TenantGroupController::class, 'toggleFeature'])->name('groups.feature');

        // KB suggestions (AJAX — returns JSON)
        Route::post('/kb/suggest', [TenantKbController::class, 'suggest'])->name('kb.suggest');

        Route::get('/tickets', [TenantTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TenantTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TenantTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TenantTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/notes', [TenantTicketController::class, 'addNote'])->name('tickets.notes');
    });

/*
|--------------------------------------------------------------------------
| Customer (end_customer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:end_customer', 'tenant'])
    ->prefix('support')
    ->name('customer.')
    ->group(function () {
        Route::get('/dashboard', fn () => inertia('Customer/Dashboard'))->name('dashboard');

        // Chat
        Route::get('/chat', [CustomerChatController::class, 'show'])->name('chat.show');
        Route::post('/chat', [CustomerChatController::class, 'start'])->name('chat.start');
        Route::post('/chat/{session}/messages', [CustomerChatController::class, 'sendMessage'])->name('chat.message');

        // KB suggestions (AJAX — returns JSON)
        Route::post('/kb/suggest', [CustomerKbController::class, 'suggest'])->name('kb.suggest');

        // Remote sessions
        Route::get('/remote/{session}/respond', [CustomerRemoteController::class, 'respond'])->name('remote.respond');
        Route::get('/remote/agent', [CustomerRemoteController::class, 'agentDownload'])->name('remote.agent');
        Route::get('/remote/agent/download', [CustomerRemoteController::class, 'downloadFile'])->name('remote.agent.download-file');
        Route::post('/remote/{session}/agent-ready', [CustomerRemoteController::class, 'agentReady'])->name('remote.agent-ready');
        Route::get('/remote/{session}/waiting', [CustomerRemoteController::class, 'waiting'])->name('remote.waiting');

        Route::get('/tickets', [CustomerTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [CustomerTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [CustomerTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [CustomerTicketController::class, 'show'])->name('tickets.show');
    });
