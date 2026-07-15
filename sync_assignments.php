<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TeachingAssignment;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

echo "Syncing TeachingAssignments with Curriculum...\n";

$assignments = TeachingAssignment::all();
$updated = 0;

foreach ($assignments as $assignment) {
    $class = SchoolClass::find($assignment->school_class_id);
    if ($class && $class->curriculum_id) {
        $curriculumSubject = DB::table('curriculum_subject')
            ->where('curriculum_id', $class->curriculum_id)
            ->where('subject_id', $assignment->subject_id)
            ->first();
            
        if ($curriculumSubject && $curriculumSubject->weekly_hours != $assignment->weekly_hours) {
            echo "Updating assignment ID {$assignment->id} (Class {$class->name}) from {$assignment->weekly_hours} to {$curriculumSubject->weekly_hours} hours.\n";
            $assignment->weekly_hours = $curriculumSubject->weekly_hours;
            $assignment->save();
            $updated++;
        }
    }
}

echo "Done. Updated {$updated} assignments.\n";
