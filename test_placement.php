<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(App\Services\TimetableEngine::class);

$a1 = App\Models\TeachingAssignment::whereHas('schoolClass', function($q) {
    $q->where('class_code', '8A1');
})->whereHas('subject', function($q) {
    $q->where('short_name', 'H');
})->first();

$a2 = App\Models\TeachingAssignment::whereHas('schoolClass', function($q) {
    $q->where('class_code', '8B1');
})->whereHas('subject', function($q) {
    $q->where('short_name', 'H');
})->first();

$blocks = [$a1, $a2];

$schedule = [];
$days = [2]; // Only Tuesday
$periods = [App\Models\Period::find(3)]; // Only Period 3
$rooms = App\Models\Room::all();

foreach ($blocks as $assignment) {
    $classId = $assignment->school_class_id;
    $teacherId = $assignment->teacher_id;
    $placed = false;

    foreach ($days as $day) {
        foreach ($periods as $period) {
            foreach ($rooms as $room) {
                // Call private isValid via Reflection
                $reflection = new ReflectionClass($engine);
                $method = $reflection->getMethod('isValid');
                $method->setAccessible(true);
                
                if ($method->invokeArgs($engine, [&$schedule, $day, $period->id, $room->id, $teacherId, $classId])) {
                    $schedule[$day][$period->id][$room->id] = $assignment;
                    $placed = true;
                    echo "Placed {$assignment->schoolClass->class_code} in room {$room->id}\n";
                    break 3;
                }
            }
        }
    }
    if (!$placed) {
        echo "Failed to place {$assignment->schoolClass->class_code}\n";
    }
}
