<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::whereNotNull('school_id')->first();
auth()->login($user);

try {
    $curricula = App\Models\Curriculum::withCount('subjects')
        ->withSum('subjects as total_hours', 'curriculum_subject.weekly_hours')
        ->orderBy('sort_order')
        ->get();
    echo $curricula->toJson();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
