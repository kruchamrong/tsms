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
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('shift_id')->constrained()->cascadeOnDelete();
            $table->integer('weekly_hours');
            $table->foreignUuid('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            // A teacher cannot be assigned the exact same configuration multiple times
            $table->unique(['teacher_id', 'subject_id', 'school_class_id', 'shift_id'], 'unique_teaching_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
