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

$alert->update(['assigned_to' => $user->id]);
$user->notify(new AlertNotification($alert));

echo "Notified user {$user->id} about alert {$alert->id}.\n";
