<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\TimetableSlot::whereHas('teachingAssignment', function($q) {
    $q->where('teacher_id', 1);
})->count();

echo "Slots for Teacher 1: " . $count . "\n";
