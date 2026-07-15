<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(App\Services\TimetableEngine::class);
$engine->generate();

$slots = App\Models\TimetableSlot::whereHas('teachingAssignment', function($q) {
    $q->whereHas('schoolClass', function($q2) {
        $q2->where('class_code', '8A1');
    });
})->get();

echo "Total slots for 8A1 after generation: " . $slots->count() . "\n";
$shift1 = 0;
$shift2 = 0;
foreach ($slots as $slot) {
    if ($slot->period->shift_id == 1) $shift1++;
    if ($slot->period->shift_id == 2) $shift2++;
}
echo "Shift 1: $shift1\n";
echo "Shift 2: $shift2\n";
