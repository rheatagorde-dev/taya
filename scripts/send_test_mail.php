<?php
// Simple script to send a test mail that will be logged by Laravel (MAIL_MAILER=log)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Send a raw mail which Laravel will route to the log driver
\Illuminate\Support\Facades\Mail::raw('This is a test log mail from TAYA.', function ($m) {
    $m->to('admin@taya.gov.ph')->subject('TAYA Test Log Mail');
});

echo "Test mail sent (should be logged).\n";
