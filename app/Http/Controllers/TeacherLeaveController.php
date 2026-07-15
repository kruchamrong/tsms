<?php

namespace App\Http\Controllers;

use App\Models\TeacherLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherLeaveController extends Controller
{
    public function index()
    {
        return Inertia::render('TeacherLeave/Index', [
            'leaves' => TeacherLeave::with('teacher')->orderBy('date_from', 'desc')->get(),
            'teachers' => Teacher::orderBy('khmer_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $validated = $request->validate([
            'teacher_id' => ['required', Rule::exists('teachers', 'id')->where($schoolIdCheck)->whereNull('deleted_at')],
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'reason' => 'nullable|string|max:255',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['status'] = 'Approved';

        TeacherLeave::create($validated);

        return redirect()->back()->with('success', 'Leave recorded successfully.');
    }

    public function destroy($id)
    {
        $leave = TeacherLeave::findOrFail($id);
        $leave->delete();
        return redirect()->back()->with('success', 'Leave record deleted.');
    }
}
