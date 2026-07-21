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
        // For Curricula table
        Schema::table('curricula', function (Blueprint $table) {
            $indexes = Schema::getIndexes('curricula');
            foreach ($indexes as $index) {
                // Find any unique index that is ONLY on the 'name' column
                if ($index['unique'] && count($index['columns']) === 1 && $index['columns'][0] === 'name') {
                    $table->dropUnique($index['name']);
                }
            }
        });

        // For Subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $indexes = Schema::getIndexes('subjects');
            foreach ($indexes as $index) {
                // Find any unique index that is ONLY on the 'subject_code' column
                if ($index['unique'] && count($index['columns']) === 1 && $index['columns'][0] === 'subject_code') {
                    $table->dropUnique($index['name']);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // It's not safe to automatically recreate these unique constraints in down() 
        // as they would violate the new composite unique constraints logic.
    }
};
