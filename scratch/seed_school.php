<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\Teacher;

$school = School::first();

// Common Cambodian last names for teachers
$lastNames = ['សុខ', 'សេង', 'ចាន់', 'ព្រាប', 'កែវ', 'អ៊ុន', 'សោម', 'ម៉ៅ', 'ប៉ែន', 'ផាត', 'ងួន', 'សួន', 'ស៊ុន', 'ទី', 'ឌី', 'លី', 'ហេង', 'ជា', 'ជួន', 'ឈន'];
$firstNames = ['សំណាង', 'បញ្ញា', 'ស្រីរ័ត្ន', 'ធារី', 'សុភា', 'ពិសិដ្ឋ', 'សុវណ្ណ', 'រតនា', 'មករា', 'វណ្ណៈ', 'វីរៈ', 'សិរី', 'ចិន្តា', 'កុសល', 'ឧត្តម', 'រិទ្ធី', 'រដ្ឋា', 'វិចិត្រ', 'ដារ៉ា', 'ធីតា'];

$teachersToInsert = [];
for ($i = 1; $i <= 30; $i++) {
    $lastName = $lastNames[array_rand($lastNames)];
    $firstName = $firstNames[array_rand($firstNames)];
    $khmerName = $lastName . ' ' . $firstName;
    
    // Fallback simple English mapping
    $enName = 'Teacher ' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $teacherCode = 'T' . str_pad($i, 3, '0', STR_PAD_LEFT);

    $gender = rand(0, 1) ? 'ប្រុស' : 'ស្រី';
    $phoneNum = '0' . rand(10, 99) . rand(100000, 999999);

    $teachersToInsert[] = [
        'school_id' => $school->id,
        'teacher_code' => $teacherCode,
        'khmer_name' => $khmerName,
        'english_name' => $enName,
        'gender' => $gender,
        'phone' => $phoneNum,
        'status' => 'សកម្ម',
        'employment_type' => 'ក្របខ័ណ្ឌ',
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

Teacher::insert($teachersToInsert);

echo "30 Teachers generated successfully for the school!\n";
