<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$indices = \DB::select("SELECT name, sql FROM sqlite_master WHERE type='index'");
print_r($indices);
