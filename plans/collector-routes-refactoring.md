# Collector Routes Refactoring Plan

## Objective

Extract all collector-related routes from [`routes/web.php`](../routes/web.php) into a dedicated [`routes/collector.php`](../routes/collector.php) file.

## Current Collector Routes in web.php

The collector routes are located in **lines 93-225** of [`routes/web.php`](../routes/web.php:93-225) and include:

### Route Groups
1. **Dashboard** (lines 95-96)
   - `/collector/dashboard`

2. **Collections** (lines 99-114)
   - `/collector/collections` - Index
   - `/collector/collections/books` - Books collection
   - `/collector/collections/cds` - CDs collection
   - `/collector/collections/vinyls` - Vinyls collection
   - `/collector/collections/home` - Home items collection
   - `/collector/collections/create` - Create collection
   - POST `/collector/collections` - Store collection

3. **Items** (lines 117-132)
   - `/collector/items` - Index
   - `/collector/items/{item}` - Show
   - `/collector/items/create` - Create
   - POST `/collector/items` - Store
   - `/collector/items/{item}/edit` - Edit
   - PUT `/collector/items/{item}` - Update
   - DELETE `/collector/items/{item}` - Destroy

4. **Locations** (lines 135-148)
   - `/collector/locations` - Index
   - `/collector/locations/create` - Create
   - POST `/collector/locations` - Store
   - `/collector/locations/{location}/edit` - Edit
   - PUT `/collector/locations/{location}` - Update
   - DELETE `/collector/locations/{location}` - Destroy

5. **Loans** (lines 151-160)
   - `/collector/loans` - Index
   - POST `/collector/loans` - Store
   - POST `/collector/loans/{loan}/return` - Return item
   - POST `/collector/loans/{loan}/extend` - Extend loan

6. **Wishlist** (lines 163-176)
   - `/collector/wishlist` - Index
   - `/collector/wishlist/create` - Create
   - POST `/collector/wishlist` - Store
   - POST `/collector/wishlist/{wishlist}/acquire` - Acquire
   - PATCH `/collector/wishlist/{wishlist}/toggle-public` - Toggle public
   - DELETE `/collector/wishlist/{wishlist}` - Destroy

7. **Export** (lines 179-194)
   - `/collector/export/collection/json` - Export collection as JSON
   - `/collector/export/collection/csv` - Export collection as CSV
   - `/collector/export/wishlist/json` - Export wishlist as JSON
   - `/collector/export/wishlist/csv` - Export wishlist as CSV
   - `/collector/export/loans/json` - Export loans as JSON
   - `/collector/export/loans/csv` - Export loans as CSV
   - `/collector/export/locations/json` - Export locations as JSON

8. **Team** (lines 197-206) - Owner only
   - `/collector/team` - Index
   - POST `/collector/team/invite` - Invite
   - PUT `/collector/team/members/{companyUser}/role` - Update role
   - DELETE `/collector/team/members/{companyUser}` - Destroy

9. **Push Notifications** (lines 209-216)
   - `/collector/push/vapid-key` - Get VAPID key
   - POST `/collector/push/subscribe` - Subscribe
   - POST `/collector/push/unsubscribe` - Unsubscribe

10. **Settings** (lines 219-224) - Owner only
    - `/collector/settings` - Index
    - PUT `/collector/settings` - Update

## Implementation Steps

### Step 1: Create `routes/collector.php`
Create a new file with the following structure:

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\Collector\DashboardController;
use App\Http\Controllers\Collector\CollectionController;
use App\Http\Controllers\Collector\ItemController;
use App\Http\Controllers\Collector\LocationController;
use App\Http\Controllers\Collector\LoanController;
use App\Http\Controllers\Collector\WishlistController;
use App\Http\Controllers\Collector\ExportController;
use App\Http\Controllers\Collector\TeamController;
use App\Http\Controllers\Collector\PushSubscriptionController;
use App\Http\Controllers\Collector\SettingsController;
use Illuminate\Support\Facades\Route;

/**
 * Collector Routes
 * 
 * Purpose: Personal inventory management for collectors
 * Middleware: auth, collector
 * Prefix: /collector
 * Authorization: Owner-only routes require 'can:manage-team' or 'can:manage-settings'
 */

Route::middleware(['auth', 'collector'])->prefix('collector')->name('collector.')->group(function () {
    // [All collector routes will be here]
});
```

### Step 2: Extract Routes from web.php
Move all collector routes (lines 93-225) from [`routes/web.php`](../routes/web.php:93-225) to the new [`routes/collector.php`](../routes/collector.php) file.

### Step 3: Update web.php
Replace the collector route group in [`routes/web.php`](../routes/web.php:93-225) with a simple include:

```php
// Include collector routes
require_once __DIR__ . '/collector.php';
```

### Step 4: Clean Up Imports
Remove any controller imports from [`routes/web.php`](../routes/web.php:1) that are now only used in [`routes/collector.php`](../routes/collector.php).

## Controllers Involved

- `App\Http\Controllers\Collector\DashboardController`
- `App\Http\Controllers\Collector\CollectionController`
- `App\Http\Controllers\Collector\ItemController`
- `App\Http\Controllers\Collector\LocationController`
- `App\Http\Controllers\Collector\LoanController`
- `App\Http\Controllers\Collector\WishlistController`
- `App\Http\Controllers\Collector\ExportController`
- `App\Http\Controllers\Collector\TeamController`
- `App\Http\Controllers\Collector\PushSubscriptionController`
- `App\Http\Controllers\Collector\SettingsController`

## Middleware Requirements

- **Base**: `auth`, `collector` (applied to all collector routes)
- **Team routes**: Additional `can:manage-team` middleware
- **Settings routes**: Additional `can:manage-settings` middleware

## Route Names

All route names will be preserved with the `collector.` prefix:
- `collector.dashboard`
- `collector.collections.index`
- `collector.collections.books`
- `collector.items.index`
- `collector.items.show`
- `collector.locations.index`
- `collector.loans.index`
- `collector.wishlist.index`
- `collector.export.collection.json`
- `collector.team.index`
- `collector.push.vapid-key`
- `collector.settings.index`

## Testing Checklist

After implementation, verify:

- [ ] All collector routes are accessible at their current URLs
- [ ] Route names remain unchanged
- [ ] Middleware is properly applied
- [ ] Owner-only routes still require appropriate permissions
- [ ] All collector functionality works as expected
- [ ] No route conflicts with other routes
- [ ] Existing tests pass

## Expected Outcome

- **Before**: [`routes/web.php`](../routes/web.php:1) has 569 lines
- **After**: [`routes/web.php`](../routes/web.php:1) reduced to ~434 lines, new [`routes/collector.php`](../routes/collector.php) with ~135 lines

## Benefits

1. **Separation of Concerns**: Collector routes are isolated in their own file
2. **Easier Maintenance**: All collector-related routes are in one place
3. **Clearer Organization**: Easy to find and modify collector routes
4. **Better Scalability**: Easy to add more collector features
5. **Consistent Pattern**: Follows existing pattern (cms.php, inventory.php, etc.)
