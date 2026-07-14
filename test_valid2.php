<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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

echo "A1 Teacher: " . $a1->teacher_id . "\n";
echo "A2 Teacher: " . $a2->teacher_id . "\n";

$schedule = [];
$schedule[2][3][1] = $a1;

$isValid = true;
foreach ($schedule[2][3] as $room => $assignedAssignment) {
    if ($assignedAssignment->teacher_id === $a2->teacher_id) {
        $isValid = false;
    }
}

echo "Is Valid: " . ($isValid ? "true" : "false") . "\n";
