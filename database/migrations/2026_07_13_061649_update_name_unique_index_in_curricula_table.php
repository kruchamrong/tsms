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
        Schema::table('curricula', function (Blueprint $table) {
            $indexes = Schema::getIndexes('curricula');
            $hasIndex = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'curricula_name_unique') {
                    $hasIndex = true;
                    break;
                }
            }
            if ($hasIndex) {
                $table->dropUnique('curricula_name_unique');
            }
            $table->unique(['name', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curricula', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_id']);
            $table->unique('name');
        });
    }
};

