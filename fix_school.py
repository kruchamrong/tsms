import os
import glob

old_code = """        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };"""

new_code = """        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };"""

files = glob.glob('app/Http/Controllers/*.php') + glob.glob('app/Http/Requests/*.php')

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if old_code in content:
        content = content.replace(old_code, new_code)
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {file}")
