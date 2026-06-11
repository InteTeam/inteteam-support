<?php

declare(strict_types=1);

use App\Http\Controllers\Collector\CollectionController;
use App\Http\Controllers\Collector\CollectionPreferencesController;
use App\Http\Controllers\Collector\DashboardController;
use App\Http\Controllers\Collector\ExportController;
use App\Http\Controllers\Collector\ItemController;
use App\Http\Controllers\Collector\LocationController;
use App\Http\Controllers\Collector\LoanController;
use App\Http\Controllers\Collector\PushSubscriptionController;
use App\Http\Controllers\Collector\SettingsController;
use App\Http\Controllers\Collector\TeamController;
use App\Http\Controllers\Collector\WishlistController;
use Illuminate\Support\Facades\Route;

/**
 * Collector Routes
 *
 * Purpose: Personal inventory management for collectors
 * Middleware: auth, collector
 * Prefix: /collector
 * Authorization: Owner-only routes require 'can:manage-team' or 'can:manage-settings'
 */

Route::middleware(['auth', 'company', 'admin'])->prefix('collector')->name('collector.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Collection Preferences
    Route::get('/preferences', [CollectionPreferencesController::class, 'edit'])
        ->name('preferences');
    Route::post('/preferences', [CollectionPreferencesController::class, 'update'])
        ->name('preferences.update');

    // Collections
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [CollectionController::class, 'index'])
            ->name('index');
        Route::get('/books', [CollectionController::class, 'books'])
            ->name('books');
        Route::get('/cds', [CollectionController::class, 'cds'])
            ->name('cds');
        Route::get('/vinyls', [CollectionController::class, 'vinyls'])
            ->name('vinyls');
        Route::get('/home', [CollectionController::class, 'home'])
            ->name('home');
        Route::get('/create', [CollectionController::class, 'create'])
            ->name('create');
        Route::post('/', [CollectionController::class, 'store'])
            ->name('store');
    });

    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])
            ->name('index');
        Route::get('/create', [ItemController::class, 'create'])
            ->name('create');
        Route::post('/', [ItemController::class, 'store'])
            ->name('store');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])
            ->name('edit');
        Route::get('/{item}', [ItemController::class, 'show'])
            ->name('show');
        Route::put('/{item}', [ItemController::class, 'update'])
            ->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])
            ->name('destroy');
    });

    // Locations
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])
            ->name('index');
        Route::get('/create', [LocationController::class, 'create'])
            ->name('create');
        Route::post('/', [LocationController::class, 'store'])
            ->name('store');
        Route::get('/{location}/edit', [LocationController::class, 'edit'])
            ->name('edit');
        Route::put('/{location}', [LocationController::class, 'update'])
            ->name('update');
        Route::delete('/{location}', [LocationController::class, 'destroy'])
            ->name('destroy');
    });

    // Loans
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])
            ->name('index');
        Route::post('/', [LoanController::class, 'store'])
            ->name('store');
        Route::post('/{loan}/return', [LoanController::class, 'return'])
            ->name('return');
        Route::post('/{loan}/extend', [LoanController::class, 'extend'])
            ->name('extend');
    });

    // Wishlist
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])
            ->name('index');
        Route::get('/create', [WishlistController::class, 'create'])
            ->name('create');
        Route::post('/', [WishlistController::class, 'store'])
            ->name('store');
        Route::post('/{wishlist}/acquire', [WishlistController::class, 'acquire'])
            ->name('acquire');
        Route::patch('/{wishlist}/toggle-public', [WishlistController::class, 'togglePublic'])
            ->name('toggle-public');
        Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])
            ->name('destroy');
    });

    // Export
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/collection/json', [ExportController::class, 'exportCollectionJson'])
            ->name('collection.json');
        Route::get('/collection/csv', [ExportController::class, 'exportCollectionCsv'])
            ->name('collection.csv');
        Route::get('/wishlist/json', [ExportController::class, 'exportWishlistJson'])
            ->name('wishlist.json');
        Route::get('/wishlist/csv', [ExportController::class, 'exportWishlistCsv'])
            ->name('wishlist.csv');
        Route::get('/loans/json', [ExportController::class, 'exportLoansJson'])
            ->name('loans.json');
        Route::get('/loans/csv', [ExportController::class, 'exportLoansCsv'])
            ->name('loans.csv');
        Route::get('/locations/json', [ExportController::class, 'exportLocationsJson'])
            ->name('locations.json');
    });

    // Team (Owner only)
    Route::middleware(['can:manage-team'])->prefix('team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])
            ->name('index');
        Route::post('/invite', [TeamController::class, 'invite'])
            ->name('invite');
        Route::put('/members/{companyUser}/role', [TeamController::class, 'updateRole'])
            ->name('update-role');
        Route::delete('/members/{companyUser}', [TeamController::class, 'destroy'])
            ->name('destroy');
    });

    // Push Notifications
    Route::prefix('push')->name('push.')->group(function () {
        Route::get('/vapid-key', [PushSubscriptionController::class, 'requestPermission'])
            ->name('vapid-key');
        Route::post('/subscribe', [PushSubscriptionController::class, 'subscribe'])
            ->name('subscribe');
        Route::post('/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
            ->name('unsubscribe');
    });

    // Settings (Owner only)
    Route::middleware(['can:manage-settings'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])
            ->name('index');
        Route::put('/', [SettingsController::class, 'update'])
            ->name('update');
    });
});
