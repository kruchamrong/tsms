<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subjects = App\Models\Subject::withoutGlobalScope(App\Models\Scopes\SchoolScope::class)->whereNull('school_id')->get(['id', 'subject_code', 'khmer_name', 'english_name', 'short_name', 'color']);
$curricula = App\Models\Curriculum::withoutGlobalScope(App\Models\Scopes\SchoolScope::class)->whereNull('school_id')->get(['id', 'name', 'grade_id']);
$periods = App\Models\Period::withoutGlobalScope(App\Models\Scopes\SchoolScope::class)->whereNull('school_id')->get(['id', 'name', 'start_time', 'end_time']);

$curriculaWithSubjects = [];
foreach ($curricula as $curriculum) {
    $curriculumSubjects = \DB::table('curriculum_subject')->where('curriculum_id', $curriculum->id)->get();
    $curriculaWithSubjects[] = [
        'name' => $curriculum->name,
        'grade_id' => $curriculum->grade_id,
        'subjects' => $curriculumSubjects->map(function($cs) use ($subjects) {
            $subject = $subjects->firstWhere('id', $cs->subject_id);
            return [
                'subject_code' => $subject->subject_code ?? '',
                'weekly_hours' => $cs->weekly_hours,
                'sort_order' => $cs->sort_order,
            ];
        })->toArray()
    ];
}

file_put_contents('exported_templates.json', json_encode([
    'subjects' => $subjects->toArray(),
    'curricula' => $curriculaWithSubjects,
    'periods' => $periods->toArray()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Exported to exported_templates.json\n";
