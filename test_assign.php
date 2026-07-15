<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assignments = App\Models\TeachingAssignment::with('subject', 'teacher')->whereHas('schoolClass', function($q) {
    $q->whereIn('class_code', ['8A1', '8B1']);
})->get();

foreach ($assignments as $a) {
    echo $a->schoolClass->class_code . ': ' . $a->subject->short_name . ' - ' . $a->teacher->khmer_name . ' (Teacher ID: ' . $a->teacher_id . ")\n";
}
