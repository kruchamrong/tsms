<?php
$models = ['AcademicYear', 'Curriculum', 'Grade', 'Period', 'Room', 'SchoolClass', 'Semester', 'Shift', 'Subject', 'SubjectGroup', 'SubstituteAssignment', 'Teacher', 'TeacherAvailability', 'TeacherAvailabilityRemark', 'TeacherDocument', 'TeacherLeave', 'TeachingAssignment', 'TimetableSlot'];
foreach ($models as $model) {
    $path = __DIR__ . '/app/Models/' . $model . '.php';
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        continue;
    }
    $content = file_get_contents($path);
    if (strpos($content, 'BelongsToSchool') === false) {
        $content = str_replace('use Illuminate\Database\Eloquent\Model;', "use Illuminate\Database\Eloquent\Model;\nuse App\Models\Traits\BelongsToSchool;", $content);
        $content = preg_replace('/class\s+'.$model.'\s+extends\s+Model\s*\{/', "class {$model} extends Model\n{\n    use BelongsToSchool;", $content);
        file_put_contents($path, $content);
        echo 'Updated ' . $model . "\n";
    }
}
