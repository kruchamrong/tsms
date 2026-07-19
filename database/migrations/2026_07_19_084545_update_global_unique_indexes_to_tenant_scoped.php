<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Teachers Table
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique('teachers_teacher_code_unique');
            $table->unique(['teacher_code', 'school_id']);
            
            // Only drop if it exists
            $indexesFound = Schema::getIndexes('teachers');
            $hasEmailUnique = collect($indexesFound)->contains(function ($index) {
                return $index['name'] === 'teachers_email_unique';
            });
            
            if($hasEmailUnique) {
                $table->dropUnique('teachers_email_unique');
                $table->unique(['email', 'school_id']);
            }
        });

        // Rooms Table
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique('rooms_room_name_unique');
            $table->unique(['room_name', 'school_id']);
        });

        // School Classes Table
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique('school_classes_class_code_unique');
            $table->unique(['class_code', 'school_id']);
        });

        // Grades Table
        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('grades_name_unique');
            $table->unique(['name', 'school_id']);
        });

        // Shifts Table
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique('shifts_name_unique');
            $table->unique(['name', 'school_id']);
        });

        // Academic Years Table
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropUnique('academic_years_name_unique');
            $table->unique(['name', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We will simply revert the unique constraints back to global unique.
        
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['teacher_code', 'school_id']);
            $table->unique('teacher_code');
            
            $indexesFound = Schema::getIndexes('teachers');
            $hasEmailSchoolIdUnique = collect($indexesFound)->contains(function ($index) {
                return $index['name'] === 'teachers_email_school_id_unique';
            });
            
            if($hasEmailSchoolIdUnique) {
                $table->dropUnique(['email', 'school_id']);
                $table->unique('email');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['room_name', 'school_id']);
            $table->unique('room_name');
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique(['class_code', 'school_id']);
            $table->unique('class_code');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_id']);
            $table->unique('name');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_id']);
            $table->unique('name');
        });

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_id']);
            $table->unique('name');
        });
    }
};
