<?php
$data = json_decode(file_get_contents('exported_templates.json'), true);

$phpCode = "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Console\Seeds\WithoutModelEvents;\nuse Illuminate\Database\Seeder;\nuse App\Models\Subject;\nuse App\Models\Curriculum;\nuse App\Models\Period;\n\nclass TemplateDataSeeder extends Seeder\n{\n    public function run(): void\n    {\n";

$phpCode .= "        \$subjects = " . var_export($data['subjects'], true) . ";\n";
$phpCode .= "        \$curricula = " . var_export($data['curricula'], true) . ";\n";
$phpCode .= "        \$periods = " . var_export($data['periods'], true) . ";\n\n";

$phpCode .= <<<'PHP'
        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['subject_code' => $subject['subject_code'], 'school_id' => null],
                [
                    'khmer_name' => $subject['khmer_name'],
                    'english_name' => $subject['english_name'],
                    'short_name' => $subject['short_name'],
                    'color' => $subject['color']
                ]
            );
        }

        foreach ($periods as $period) {
            Period::firstOrCreate(
                ['name' => $period['name'], 'school_id' => null],
                [
                    'start_time' => $period['start_time'],
                    'end_time' => $period['end_time']
                ]
            );
        }

        foreach ($curricula as $curriculum) {
            $curr = Curriculum::firstOrCreate(
                ['name' => $curriculum['name'], 'school_id' => null],
                ['grade_id' => $curriculum['grade_id']]
            );
            
            // Sync subjects
            $syncData = [];
            foreach ($curriculum['subjects'] as $cs) {
                $subject = Subject::where('subject_code', $cs['subject_code'])->whereNull('school_id')->first();
                if ($subject) {
                    $syncData[$subject->id] = [
                        'weekly_hours' => $cs['weekly_hours'],
                        'sort_order' => $cs['sort_order']
                    ];
                }
            }
            $curr->subjects()->sync($syncData);
        }
    }
}
PHP;

file_put_contents('database/seeders/TemplateDataSeeder.php', $phpCode);
echo "TemplateDataSeeder created.\n";
