<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL for Postgres to allow type casting from bigint to varchar
        DB::statement('ALTER TABLE audits ALTER COLUMN auditable_id TYPE VARCHAR(36) USING auditable_id::VARCHAR(36)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Reverting might fail if there are UUIDs in the column
            // DB::statement('ALTER TABLE audits ALTER COLUMN auditable_id TYPE BIGINT USING auditable_id::BIGINT');
        });
    }
};
