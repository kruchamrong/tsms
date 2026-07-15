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
        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week'); // 1 = Monday, 7 = Sunday
            $table->foreignId('shift_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            // Unique availability per day and shift for a teacher
            $table->unique(['teacher_id', 'day_of_week', 'shift_id'], 'unique_teacher_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_availabilities');
    }
};

