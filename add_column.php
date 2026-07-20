<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!Schema::hasColumn('schools', 'trial_ends_at')) {
    Schema::table('schools', function (Blueprint $table) {
        $table->timestamp('trial_ends_at')->nullable();
    });
    echo "Column trial_ends_at added successfully.\n";
} else {
    echo "Column trial_ends_at already exists.\n";
}

DB::table('migrations')->insertOrIgnore([
    'migration' => '2026_07_20_043413_add_trial_ends_at_to_schools_table',
    'batch' => 2
]);
