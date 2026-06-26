<?php

namespace App\Http\Controllers;

use App\Models\TeacherLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TeacherLeaveController extends Controller
{
    public function index()
    {
        return Inertia::render('TeacherLeave/Index', [
            'leaves' => TeacherLeave::with('teacher')->orderBy('date_from', 'desc')->get(),
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:255',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['status'] = 'Approved';

        TeacherLeave::create($validated);

        return redirect()->back()->with('success', 'Leave recorded successfully.');
    }

    public function destroy(TeacherLeave $teacherLeave)
    {
        $teacherLeave->delete();
        return redirect()->back()->with('success', 'Leave record deleted.');
    }
}
