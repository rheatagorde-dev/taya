<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PhaseComplianceService;
use App\Models\Detainee;

$d = Detainee::first();
if (! $d) {
    echo "No detainees found.\n";
    exit(1);
}

app(PhaseComplianceService::class)->computeOverstay($d);

echo "computeOverstay run for detainee {$d->id}\n";
