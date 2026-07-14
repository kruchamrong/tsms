<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assignment = App\Models\TeachingAssignment::first();
echo "Type: " . gettype($assignment->teacher_id) . "\n";
