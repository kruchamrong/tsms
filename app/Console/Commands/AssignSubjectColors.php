<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:assign-subject-colors')]
#[Description('Command description')]
class AssignSubjectColors extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subjects = \App\Models\Subject::all();
        
        $colorMap = [
            'ខ្មែរ' => '#2563EB', // Blue
            'គណិត' => '#DC2626', // Red
            'រូប' => '#9333EA', // Purple
            'គីមី' => '#D97706', // Amber/Orange
            'ជីវ' => '#16A34A', // Green
            'ផែនដី' => '#65A30D', // Lime/Olive
            'ប្រវត្តិ' => '#92400E', // Brown/Dark Amber
            'ភូមិ' => '#059669', // Emerald/Teal
            'សីលធម៌' => '#4F46E5', // Indigo
            'ពលរដ្ឋ' => '#4F46E5', // Indigo
            'អង់គ្លេស' => '#DB2777', // Pink
            'កីឡា' => '#EA580C', // Orange
            'សិល្បៈ' => '#C026D3', // Fuchsia
            'កុំព្យូទ័រ' => '#0891B2', // Cyan
            'ICT' => '#0891B2', // Cyan
            'បច្ចេកវិទ្យា' => '#0891B2', // Cyan
            'សេដ្ឋកិច្ច' => '#0F766E', // Teal
        ];

        $defaultColor = '#64748B'; // Slate

        foreach ($subjects as $subject) {
            $assigned = false;
            foreach ($colorMap as $keyword => $color) {
                if (str_contains(mb_strtolower($subject->khmer_name), mb_strtolower($keyword)) || 
                    str_contains(mb_strtolower($subject->english_name), mb_strtolower($keyword))) {
                    $subject->update(['color' => $color]);
                    $assigned = true;
                    $this->info("Assigned $color to {$subject->khmer_name}");
                    break;
                }
            }
            if (!$assigned) {
                $subject->update(['color' => $defaultColor]);
                $this->info("Assigned default $defaultColor to {$subject->khmer_name}");
            }
        }
        
        $this->info('Colors assigned successfully.');
    }
}
