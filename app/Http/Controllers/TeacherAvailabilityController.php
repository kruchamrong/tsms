<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TeacherAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $availabilities = [];
        if ($teacherId) {
            $availabilities = TeacherAvailability::where('teacher_id', $teacherId)->get();
        }
            
        return Inertia::render('TeacherAvailability/Index', [
            'teachers' => Teacher::all(),
            'shifts' => Shift::all(),
            'selectedTeacherId' => $teacherId,
            'availabilities' => $availabilities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'availabilities' => 'array',
            'availabilities.*.day_of_week' => 'required|integer|min:1|max:7',
            'availabilities.*.shift_id' => 'required|exists:shifts,id',
            'availabilities.*.is_available' => 'boolean',
        ]);

        TeacherAvailability::where('teacher_id', $validated['teacher_id'])->delete();

        if (!empty($validated['availabilities'])) {
            $insertData = collect($validated['availabilities'])->filter(function($item) {
                return $item['is_available'];
            })->map(function ($item) use ($validated) {
                return [
                    'id' => (string) Str::uuid(),
                    'teacher_id' => $validated['teacher_id'],
                    'day_of_week' => $item['day_of_week'],
                    'shift_id' => $item['shift_id'],
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            
            if (count($insertData) > 0) {
                TeacherAvailability::insert($insertData);
            }
        }

        return redirect()->back()->with('success', 'Availabilities saved successfully.');
    }
}
