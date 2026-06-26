<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $assignments = TeachingAssignment::with(['teacher', 'subject', 'schoolClass.grade', 'shift'])
            ->when($teacherId, function ($query, $teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->get();
            
        return Inertia::render('TeachingAssignment/Index', [
            'assignments' => $assignments,
            'teachers' => Teacher::all(),
            'subjects' => Subject::all(),
            'classes' => SchoolClass::with('grade')->get(),
            'shifts' => Shift::all(),
            'selectedTeacherId' => $teacherId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'shift_id' => 'required|exists:shifts,id',
            'weekly_hours' => 'required|integer|min:1',
        ]);

        TeachingAssignment::create($validated);

        return redirect()->back()->with('success', 'Assignment created successfully.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->delete();
        return redirect()->back()->with('success', 'Assignment deleted successfully.');
    }
}
