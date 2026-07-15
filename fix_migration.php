<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$tables = ['academic_years', 'curricula', 'grades', 'periods', 'rooms', 'school_classes', 'semesters', 'shifts', 'subjects', 'subject_groups', 'substitute_assignments', 'teachers', 'teacher_availabilities', 'teacher_availability_remarks', 'teacher_documents', 'teacher_leaves', 'teaching_assignments', 'timetable_slots'];

foreach ($tables as $table) {
    if (!Schema::hasColumn($table, 'school_id')) {
        Schema::table($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
        });
        echo "Added school_id to $table\n";
    } else {
        echo "$table already has school_id\n";
    }
}

// Mark migration as done
Illuminate\Support\Facades\DB::table('migrations')->insert([
    'migration' => '2026_07_06_045602_add_school_id_to_all_tenant_tables',
    'batch' => 3
]);
