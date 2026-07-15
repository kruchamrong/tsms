<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Teacher;

try {
    $schoolIdCheck = function ($query) {
        return null;
    };

    $rule = Rule::exists('teachers', 'id')->where($schoolIdCheck)->whereNull('deleted_at');
    
    $v = Validator::make(['teacher_id' => Teacher::first()->id], [
        'teacher_id' => ['required', $rule]
    ]);
    
    dump($v->passes());
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
