<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assignments = App\Models\TeachingAssignment::whereHas('schoolClass', function($q) {
    $q->where('class_code', '8A1');
})->get();

foreach ($assignments as $a) {
    echo "ID: {$a->id}, Shift: {$a->shift_id}, Hours: {$a->weekly_hours}\n";
}
