<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Detainee;

$count = 0;

foreach (Detainee::whereNull('tracking_code')->orWhere('tracking_code', '')->cursor() as $detainee) {
    do {
        $code = 'TAYA-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
    } while (Detainee::where('tracking_code', $code)->exists());

    $detainee->tracking_code = $code;
    $detainee->save();
    $count++;
}

echo "Updated detainees: {$count}\n";
