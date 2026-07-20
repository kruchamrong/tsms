<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;

// Update the first school as a test
$school = School::first();
if ($school) {
    $school->trial_ends_at = now()->addDays(2);
    $school->save();
    echo "School '{$school->name}' trial set to expire in 2 days.\n";
} else {
    echo "No school found.\n";
}
