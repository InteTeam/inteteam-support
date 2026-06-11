<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'admin@test.com')->first();

if ($user) {
    echo "✅ User found:\n";
    echo "   Email: {$user->email}\n";
    echo "   Name: {$user->name}\n";
    echo "   ID: {$user->id}\n";
    echo "   Companies: " . $user->companies()->count() . "\n";
    
    $company = $user->companies()->first();
    if ($company) {
        echo "   Company: {$company->name}\n";
        $pivot = $user->companies()->first()->pivot;
        echo "   Role ID: " . $pivot->role_id . "\n";
        echo "   Accepted: " . ($pivot->accepted_at ? 'Yes' : 'No') . "\n";
    }
    
    // Reset password
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo "\n🔑 Password has been reset to: password\n";
} else {
    echo "❌ User admin@test.com not found\n";
}
