<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assignments = App\Models\TeachingAssignment::with(['subject', 'schoolClass'])->where('teacher_id', 105)->get();
foreach ($assignments as $a) {
    echo "Class: " . $a->schoolClass->class_code . " | Subject: " . $a->subject->short_name . "\n";
}
