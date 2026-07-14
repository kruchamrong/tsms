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
        Schema::table('subjects', function (Blueprint $table) {
            $indexes = Schema::getIndexes('subjects');
            $hasIndex = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'subjects_subject_code_unique') {
                    $hasIndex = true;
                    break;
                }
            }
            if ($hasIndex) {
                $table->dropUnique('subjects_subject_code_unique');
            }
            $table->unique(['subject_code', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['subject_code', 'school_id']);
            $table->unique('subject_code');
        });
    }
};

