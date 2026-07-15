<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teachers = App\Models\Teacher::pluck('khmer_name')->toArray();
print_r($teachers);
