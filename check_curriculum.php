<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$curriculum = \App\Models\Curriculum::with('subjects')->where('name', 'ថ្នាក់ទី៧')->first();
foreach($curriculum->subjects as $s) {
    echo $s->khmer_name . ' | ' . $s->pivot->weekly_hours . "\n";
}
