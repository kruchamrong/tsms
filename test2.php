<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Grades:\n";
echo App\Models\Grade::all()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
echo "Shifts:\n";
echo App\Models\Shift::all()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
