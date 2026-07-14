<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\Teacher::where('khmer_name', 'like', '%ពេជ្រ ចំរុង%')->first();
if ($teacher) {
    $assignments = App\Models\TeachingAssignment::with(['subject', 'schoolClass'])->where('teacher_id', $teacher->id)->get();
    foreach ($assignments as $a) {
        echo "Assignment ID: {$a->id}, Assignment Shift: {$a->shift_id}, Class Shift: {$a->schoolClass->shift_id}\n";
    }
}
