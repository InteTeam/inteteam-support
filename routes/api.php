<?php

use App\Http\Controllers\Api\V1\AgentRegistrationController;
use App\Http\Controllers\Api\V1\ProvisioningController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel Provisioning API (inteteam-panel → inteteam-support)
|--------------------------------------------------------------------------
*/
Route::middleware(['panel.token', 'throttle:30,1'])
    ->prefix('v1/provisioning')
    ->name('api.provisioning.')
    ->group(function () {
        Route::post('/tenants', [ProvisioningController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{slug}', [ProvisioningController::class, 'show'])->name('tenants.show');
        Route::post('/tenants/{slug}/suspend', [ProvisioningController::class, 'suspend'])->name('tenants.suspend');
    });

/*
|--------------------------------------------------------------------------
| Agent Registration (Windows desktop agent → inteteam-support)
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:10,1'])
    ->prefix('v1/agent')
    ->name('api.agent.')
    ->group(function () {
        Route::post('/register', [AgentRegistrationController::class, 'register'])->name('register');
    });
