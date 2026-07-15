<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('teachers'))->pluck('name')->toArray();
            
            if (in_array('teachers_teacher_code_unique', $indexes)) {
                $table->dropUnique('teachers_teacher_code_unique');
            }
            if (!in_array('teachers_teacher_code_school_id_unique', $indexes)) {
                $table->unique(['teacher_code', 'school_id']);
            }
            
            if (in_array('teachers_email_unique', $indexes)) {
                $table->dropUnique('teachers_email_unique');
            }
            if (!in_array('teachers_email_school_id_unique', $indexes)) {
                $table->unique(['email', 'school_id']);
            }
        });

        // Update grades table
        Schema::table('grades', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('grades'))->pluck('name')->toArray();
            if (in_array('grades_name_unique', $indexes)) {
                $table->dropUnique('grades_name_unique');
            }
            if (!in_array('grades_name_school_id_unique', $indexes)) {
                $table->unique(['name', 'school_id']);
            }
        });

        // Update rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('rooms'))->pluck('name')->toArray();
            if (in_array('rooms_room_name_unique', $indexes)) {
                $table->dropUnique('rooms_room_name_unique');
            }
            if (!in_array('rooms_room_name_school_id_unique', $indexes)) {
                $table->unique(['room_name', 'school_id']);
            }
        });

        // Update school_classes table
        Schema::table('school_classes', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('school_classes'))->pluck('name')->toArray();
            if (in_array('school_classes_class_code_unique', $indexes)) {
                $table->dropUnique('school_classes_class_code_unique');
            }
            if (!in_array('school_classes_class_code_school_id_unique', $indexes)) {
                $table->unique(['class_code', 'school_id']);
            }
        });

        // Update shifts table
        Schema::table('shifts', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('shifts'))->pluck('name')->toArray();
            if (in_array('shifts_name_unique', $indexes)) {
                $table->dropUnique('shifts_name_unique');
            }
            if (!in_array('shifts_name_school_id_unique', $indexes)) {
                $table->unique(['name', 'school_id']);
            }
        });

        // Update academic_years table
        Schema::table('academic_years', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('academic_years'))->pluck('name')->toArray();
            if (in_array('academic_years_name_unique', $indexes)) {
                $table->dropUnique('academic_years_name_unique');
            }
            if (!in_array('academic_years_name_school_id_unique', $indexes)) {
                $table->unique(['name', 'school_id']);
            }
        });
    }

    public function down(): void
    {
    }
};
