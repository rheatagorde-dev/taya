<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alert;
use App\Models\User;
use App\Notifications\AlertNotification;

$alert = Alert::first();
if (! $alert) {
    echo "No alerts found in database.\n";
    exit(1);
}

$user = User::first();
if (! $user) {
    echo "No users found in database.\n";
    exit(1);
}

// Simulate an admin override to 'critical' and assign to the first user
$alert->update([
    'alert_level' => 'critical',
    'admin_override' => true,
    'override_note' => 'Test override via script',
    'assigned_to' => $user->id,
]);

// Notify assigned user (mirrors the controller behavior)
$user->notify(new AlertNotification($alert));

echo "Admin override simulated: alerted user {$user->id} about alert {$alert->id}.\n";
