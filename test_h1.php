<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacherIds = App\Models\TeachingAssignment::whereHas('schoolClass', function($q) {
    $q->whereIn('class_code', ['8A1', '8B1']);
})->whereHas('subject', function($q) {
    $q->where('short_name', 'H1');
})->pluck('teacher_id', 'school_class_id');

print_r($teacherIds->toArray());
