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
        Schema::table('school_classes', function (Blueprint $table) {
            if (Schema::hasColumn('school_classes', 'subject_group_id')) {
                $table->dropForeign(['subject_group_id']);
                $table->dropColumn('subject_group_id');
            }
            $table->foreignId('curriculum_id')->nullable()->after('grade_id')->constrained('curricula')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['curriculum_id']);
            $table->dropColumn('curriculum_id');
            $table->foreignId('subject_group_id')->nullable()->constrained('subject_groups')->nullOnDelete();
        });
    }
};

