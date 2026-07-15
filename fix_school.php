<?php
$files = array_merge(glob('app/Http/Controllers/*.php'), glob('app/Http/Requests/*.php'));

$oldCode = <<<EOT
        \$schoolIdCheck = function (\$query) {
            if (auth()->check() && auth()->user()->school_id) {
                return \$query->where('school_id', auth()->user()->school_id);
            }
        };
EOT;

$newCode = <<<EOT
        \$schoolIdCheck = function (\$query) {
            if (auth()->check() && auth()->user()->school_id) {
                return \$query->where(function(\$q) {
                    \$q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };
EOT;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, $oldCode) !== false) {
        $content = str_replace($oldCode, $newCode, $content);
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    }
}
