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
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex('audits_auditable_type_auditable_id_index');
        });

        // Use raw SQL for Postgres to allow type casting from bigint to varchar
        DB::statement('ALTER TABLE audits ALTER COLUMN auditable_id TYPE VARCHAR(36) USING auditable_id::VARCHAR(36)');

        Schema::table('audits', function (Blueprint $table) {
            $table->index(['auditable_type', 'auditable_id'], 'audits_auditable_type_auditable_id_index');
        });
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
