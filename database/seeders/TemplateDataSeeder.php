<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\Period;

class TemplateDataSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = array (
  0 => 
  array (
    'id' => 1,
    'subject_code' => 'S01',
    'khmer_name' => 'ភាសាខ្មែរ',
    'english_name' => 'Khmer Language',
    'short_name' => 'K',
    'color' => '#2563EB',
  ),
  1 => 
  array (
    'id' => 2,
    'subject_code' => 'S02',
    'khmer_name' => 'សីលធម៌-ពលរដ្ឋ',
    'english_name' => 'Civics & Morals',
    'short_name' => 'CM',
    'color' => '#4F46E5',
  ),
  2 => 
  array (
    'id' => 3,
    'subject_code' => 'S04',
    'khmer_name' => 'ប្រវត្តិវិទ្យា',
    'english_name' => 'History',
    'short_name' => 'H',
    'color' => '#92400E',
  ),
  3 => 
  array (
    'id' => 4,
    'subject_code' => 'S03',
    'khmer_name' => 'ភូមិវិទ្យា',
    'english_name' => 'Geography',
    'short_name' => 'G',
    'color' => '#059669',
  ),
  4 => 
  array (
    'id' => 5,
    'subject_code' => 'S05',
    'khmer_name' => 'គណិតវិទ្យា',
    'english_name' => 'Mathematics',
    'short_name' => 'M',
    'color' => '#DC2626',
  ),
  5 => 
  array (
    'id' => 6,
    'subject_code' => 'S06',
    'khmer_name' => 'រូបវិទ្យា',
    'english_name' => 'Physics',
    'short_name' => 'P',
    'color' => '#9333EA',
  ),
  6 => 
  array (
    'id' => 7,
    'subject_code' => 'S07',
    'khmer_name' => 'គីមីវិទ្យា',
    'english_name' => 'Chemistry',
    'short_name' => 'C',
    'color' => '#D97706',
  ),
  7 => 
  array (
    'id' => 8,
    'subject_code' => 'S08',
    'khmer_name' => 'ជីវវិទ្យា',
    'english_name' => 'Biology',
    'short_name' => 'B',
    'color' => '#16A34A',
  ),
  8 => 
  array (
    'id' => 9,
    'subject_code' => 'S09',
    'khmer_name' => 'ផែនដីវិទ្យា',
    'english_name' => 'Earth Science',
    'short_name' => 'ES',
    'color' => '#65A30D',
  ),
  9 => 
  array (
    'id' => 10,
    'subject_code' => 'S10',
    'khmer_name' => 'ភាសាអង់គ្លេស',
    'english_name' => 'English Language',
    'short_name' => 'EL',
    'color' => '#DB2777',
  ),
  10 => 
  array (
    'id' => 11,
    'subject_code' => 'S11',
    'khmer_name' => 'គេហវិទ្យា',
    'english_name' => 'Home Economics',
    'short_name' => 'HE',
    'color' => '#64748B',
  ),
  11 => 
  array (
    'id' => 12,
    'subject_code' => 'S13',
    'khmer_name' => 'បច្ចេកវិទ្យា',
    'english_name' => 'Technology / ICT',
    'short_name' => 'ICT',
    'color' => '#0891B2',
  ),
  12 => 
  array (
    'id' => 13,
    'subject_code' => 'S15',
    'khmer_name' => 'សេដ្ឋកិច្ច',
    'english_name' => 'Economics',
    'short_name' => 'Ec',
    'color' => '#0F766E',
  ),
  13 => 
  array (
    'id' => 14,
    'subject_code' => 'S16',
    'khmer_name' => 'អប់រំសុខភាព',
    'english_name' => 'Health Education',
    'short_name' => 'HE',
    'color' => '#8ab9ff',
  ),
  14 => 
  array (
    'id' => 15,
    'subject_code' => 'S14',
    'khmer_name' => 'អប់រំកាយ-កីឡា',
    'english_name' => 'Physical Education',
    'short_name' => 'PE',
    'color' => '#EA580C',
  ),
  15 => 
  array (
    'id' => 16,
    'subject_code' => 'S12',
    'khmer_name' => 'អប់រំសិល្បៈ',
    'english_name' => 'Art Education',
    'short_name' => 'AE',
    'color' => '#C026D3',
  ),
);
        $curricula = array (
  0 => 
  array (
    'name' => 'ថ្នាក់ទី១០-វិទ្យាសាស្ត្រពិត',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 5,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 6,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 4,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 3,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 3,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  1 => 
  array (
    'name' => 'ថ្នាក់ទី១០-វិទ្យាសាស្ត្រសង្គម',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 6,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 3,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 4,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 3,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 5,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 2,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  2 => 
  array (
    'name' => 'ថ្នាក់ទី១១-វិទ្យាសាស្ត្រពិត',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 5,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 6,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 4,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 3,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 3,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  3 => 
  array (
    'name' => 'ថ្នាក់ទី១១-វិទ្យាសាស្ត្រសង្គម',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 6,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 3,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 4,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 3,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 5,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 2,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  4 => 
  array (
    'name' => 'ថ្នាក់ទី១២-វិទ្យាសាស្ត្រពិត',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 5,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 6,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 4,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 3,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 3,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  5 => 
  array (
    'name' => 'ថ្នាក់ទី១២-វិទ្យាសាស្ត្រសង្គម',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 6,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 3,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 4,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 3,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 5,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 2,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 2,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 14,
      ),
      13 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 12,
      ),
    ),
  ),
  6 => 
  array (
    'name' => 'ថ្នាក់ទី៧',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 7,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 7,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 1,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 1,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S11',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 2,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 15,
      ),
      13 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 16,
      ),
      14 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 14,
      ),
      15 => 
      array (
        'subject_code' => 'S12',
        'weekly_hours' => 1,
        'sort_order' => 12,
      ),
    ),
  ),
  7 => 
  array (
    'name' => 'ថ្នាក់ទី៨',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 7,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 7,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 1,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 1,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S11',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 2,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 15,
      ),
      13 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 16,
      ),
      14 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 14,
      ),
      15 => 
      array (
        'subject_code' => 'S12',
        'weekly_hours' => 1,
        'sort_order' => 12,
      ),
    ),
  ),
  8 => 
  array (
    'name' => 'ថ្នាក់ទី៩',
    'grade_id' => NULL,
    'subjects' => 
    array (
      0 => 
      array (
        'subject_code' => 'S01',
        'weekly_hours' => 7,
        'sort_order' => 1,
      ),
      1 => 
      array (
        'subject_code' => 'S02',
        'weekly_hours' => 2,
        'sort_order' => 2,
      ),
      2 => 
      array (
        'subject_code' => 'S04',
        'weekly_hours' => 2,
        'sort_order' => 4,
      ),
      3 => 
      array (
        'subject_code' => 'S03',
        'weekly_hours' => 2,
        'sort_order' => 3,
      ),
      4 => 
      array (
        'subject_code' => 'S05',
        'weekly_hours' => 7,
        'sort_order' => 5,
      ),
      5 => 
      array (
        'subject_code' => 'S06',
        'weekly_hours' => 2,
        'sort_order' => 6,
      ),
      6 => 
      array (
        'subject_code' => 'S07',
        'weekly_hours' => 1,
        'sort_order' => 7,
      ),
      7 => 
      array (
        'subject_code' => 'S08',
        'weekly_hours' => 2,
        'sort_order' => 8,
      ),
      8 => 
      array (
        'subject_code' => 'S09',
        'weekly_hours' => 1,
        'sort_order' => 9,
      ),
      9 => 
      array (
        'subject_code' => 'S10',
        'weekly_hours' => 6,
        'sort_order' => 10,
      ),
      10 => 
      array (
        'subject_code' => 'S11',
        'weekly_hours' => 1,
        'sort_order' => 11,
      ),
      11 => 
      array (
        'subject_code' => 'S13',
        'weekly_hours' => 2,
        'sort_order' => 13,
      ),
      12 => 
      array (
        'subject_code' => 'S15',
        'weekly_hours' => 1,
        'sort_order' => 15,
      ),
      13 => 
      array (
        'subject_code' => 'S16',
        'weekly_hours' => 1,
        'sort_order' => 16,
      ),
      14 => 
      array (
        'subject_code' => 'S14',
        'weekly_hours' => 2,
        'sort_order' => 14,
      ),
      15 => 
      array (
        'subject_code' => 'S12',
        'weekly_hours' => 1,
        'sort_order' => 12,
      ),
    ),
  ),
);
        $periods = array (
  0 => 
  array (
    'id' => 1,
    '"name"' => 'name',
    'start_time' => '07:00:00',
    'end_time' => '08:00:00',
  ),
  1 => 
  array (
    'id' => 2,
    '"name"' => 'name',
    'start_time' => '08:00:00',
    'end_time' => '09:00:00',
  ),
  2 => 
  array (
    'id' => 3,
    '"name"' => 'name',
    'start_time' => '09:00:00',
    'end_time' => '10:00:00',
  ),
  3 => 
  array (
    'id' => 4,
    '"name"' => 'name',
    'start_time' => '10:00:00',
    'end_time' => '11:00:00',
  ),
  4 => 
  array (
    'id' => 5,
    '"name"' => 'name',
    'start_time' => '13:00:00',
    'end_time' => '14:00:00',
  ),
  5 => 
  array (
    'id' => 6,
    '"name"' => 'name',
    'start_time' => '14:00:00',
    'end_time' => '15:00:00',
  ),
  6 => 
  array (
    'id' => 7,
    '"name"' => 'name',
    'start_time' => '15:00:00',
    'end_time' => '16:00:00',
  ),
  7 => 
  array (
    'id' => 8,
    '"name"' => 'name',
    'start_time' => '16:00:00',
    'end_time' => '17:00:00',
  ),
);

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

        $shiftMorning = \App\Models\Shift::firstOrCreate(['name' => 'ព្រឹក', 'school_id' => null]);
        $shiftAfternoon = \App\Models\Shift::firstOrCreate(['name' => 'រសៀល', 'school_id' => null]);

        $morningPeriods = [
            ['start' => '07:00:00', 'end' => '08:00:00'],
            ['start' => '08:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '11:00:00'],
        ];

        $afternoonPeriods = [
            ['start' => '13:00:00', 'end' => '14:00:00'],
            ['start' => '14:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '16:00:00'],
            ['start' => '16:00:00', 'end' => '17:00:00'],
        ];

        foreach ($morningPeriods as $p) {
            Period::firstOrCreate([
                'start_time' => $p['start'],
                'end_time' => $p['end'],
                'shift_id' => $shiftMorning->id,
                'school_id' => null
            ]);
        }

        foreach ($afternoonPeriods as $p) {
            Period::firstOrCreate([
                'start_time' => $p['start'],
                'end_time' => $p['end'],
                'shift_id' => $shiftAfternoon->id,
                'school_id' => null
            ]);
        }

        foreach ($curricula as $curriculum) {
            $curr = Curriculum::firstOrCreate(
                ['name' => $curriculum['name'], 'school_id' => null],
                []
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