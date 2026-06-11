<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Company;
use App\Models\Form;
use App\Models\Location;
use App\Models\SchedulerConfig;
use App\Services\FormSchedulerService;

$company = Company::factory()->create();
$location = Location::factory()->create(['company_id' => $company->id]);

$config = SchedulerConfig::factory()->create([
    'company_id' => $company->id,
    'location_id' => $location->id,
    'max_bookings_per_day' => 5,
]);

$form = Form::factory()->create([
    'company_id' => $company->id,
    'scheduler_enabled' => true,
    'default_location_id' => $location->id,
]);

// Create 5 bookings
$bookings = Booking::factory()->count(5)->create([
    'company_id' => $company->id,
    'location_id' => $location->id,
    'created_at' => now(),
]);

echo "Created bookings: " . $bookings->count() . "\n";
echo "Location ID: " . $location->id . "\n";
echo "Form default_location_id: " . $form->default_location_id . "\n";

// Check raw count
$rawCount = Booking::where('location_id', $location->id)->count();
echo "Raw count all bookings for location: " . $rawCount . "\n";

$dateCount = Booking::where('location_id', $location->id)
    ->whereDate('created_at', now())
    ->count();
echo "Count with whereDate: " . $dateCount . "\n";

// Check service
$service = app(FormSchedulerService::class);
$serviceCount = $service->getBookingCountForDate($location->id, now());
echo "Service getBookingCountForDate: " . $serviceCount . "\n";

$result = $service->isDailyCapReached($form, now());
echo "isDailyCapReached: " . ($result ? 'true' : 'false') . "\n";
