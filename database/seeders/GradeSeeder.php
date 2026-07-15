<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            ['name' => 'ថ្នាក់ទី៧'],
            ['name' => 'ថ្នាក់ទី៨'],
            ['name' => 'ថ្នាក់ទី៩'],
            ['name' => 'ថ្នាក់ទី១០'],
            ['name' => 'ថ្នាក់ទី១១'],
            ['name' => 'ថ្នាក់ទី១២'],
        ];

        foreach ($grades as $grade) {
            \App\Models\Grade::firstOrCreate(['name' => $grade['name']]);
        }
    }
}
