<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slots = App\Models\TimetableSlot::with(['teachingAssignment.schoolClass', 'teachingAssignment.subject'])->where('day_of_week', 2)->whereHas('teachingAssignment', function($q) {
    $q->whereHas('schoolClass', function($q2) {
        $q2->where('class_code', '8A1');
    });
})->orderBy('period_id')->get();

foreach ($slots as $s) {
    echo 'Period ' . $s->period_id . ': ' . $s->teachingAssignment->subject->short_name . "\n";
}
