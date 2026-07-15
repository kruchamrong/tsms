<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\Teacher::where('khmer_name', 'like', '%ពេជ្រ ចំរុង%')->first();
if ($teacher) {
    echo "Teacher found: {$teacher->khmer_name}\n";
    $assignments = App\Models\TeachingAssignment::with(['subject', 'schoolClass'])->where('teacher_id', $teacher->id)->get();
    foreach ($assignments as $a) {
        $shift = $a->schoolClass ? $a->schoolClass->shift_id : 'Unknown';
        echo "Class: {$a->schoolClass->name} (Shift: {$shift}), Subject: {$a->subject->khmer_name}, Hours: {$a->weekly_hours}\n";
        
        $slots = App\Models\TimetableSlot::with('period')->where('teaching_assignment_id', $a->id)->get();
        echo "  Total Slots Assigned: " . $slots->count() . "\n";
        foreach ($slots as $s) {
            echo "    Day: {$s->day_of_week}, Period: {$s->period_id} (Shift: {$s->period->shift_id})\n";
        }
    }
}
