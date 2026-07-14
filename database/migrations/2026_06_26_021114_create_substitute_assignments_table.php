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
        Schema::create('substitute_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('timetable_slot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('Assigned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitute_assignments');
    }
};

