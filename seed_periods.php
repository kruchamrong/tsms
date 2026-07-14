<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::table('periods')->truncate();

$periods = [
    // Morning (shift_id 1)
    ['start_time' => '07:00:00', 'end_time' => '08:00:00', 'shift_id' => 1, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '08:00:00', 'end_time' => '09:00:00', 'shift_id' => 1, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '09:00:00', 'end_time' => '10:00:00', 'shift_id' => 1, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '10:00:00', 'end_time' => '11:00:00', 'shift_id' => 1, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],

    // Afternoon (shift_id 2)
    ['start_time' => '13:00:00', 'end_time' => '14:00:00', 'shift_id' => 2, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '14:00:00', 'end_time' => '15:00:00', 'shift_id' => 2, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '15:00:00', 'end_time' => '16:00:00', 'shift_id' => 2, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ['start_time' => '16:00:00', 'end_time' => '17:00:00', 'shift_id' => 2, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
];

DB::table('periods')->insert($periods);
echo "Periods seeded successfully.\n";
