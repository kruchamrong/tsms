<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$class = \App\Models\SchoolClass::where('name', '7A1')->orWhere('class_code', '7A1')->first();
$gradeId = $class->grade_id;

$curricula = \App\Models\Curriculum::with('subject')->where('grade_id', $gradeId)->get();
echo "--- CURRICULUM ---\n";
foreach($curricula as $c) {
    echo $c->subject->khmer_name . ' | ' . $c->weekly_hours . "\n";
}
echo 'Total Curriculum: ' . $curricula->sum('weekly_hours') . "\n\n";

$assignments = \App\Models\TeachingAssignment::with('subject', 'teacher')->where('school_class_id', $class->id)->get();
echo "--- ASSIGNMENTS 7A1 ---\n";
foreach($assignments as $a) {
    echo $a->subject->khmer_name . ' | ' . $a->teacher->khmer_name . ' | ' . $a->weekly_hours . "\n";
}
echo 'Total Assignments: ' . $assignments->sum('weekly_hours') . "\n";
