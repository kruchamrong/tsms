<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'academic_years', 'curricula', 'grades', 'periods', 'rooms', 
        'school_classes', 'semesters', 'shifts', 'subjects', 'subject_groups', 
        'substitute_assignments', 'teachers', 'teacher_availabilities', 
        'teacher_availability_remarks', 'teacher_documents', 'teacher_leaves', 
        'teaching_assignments', 'timetable_slots'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropForeign(['school_id']);
                $tableBlueprint->dropColumn('school_id');
            });
        }
    }
};
