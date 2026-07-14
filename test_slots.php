<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$periods = App\Models\Period::all();
foreach($periods as $p) {
    echo "ID: {$p->id}, Shift: {$p->shift_id}, Time: {$p->start_time} - {$p->end_time}\n";
}
