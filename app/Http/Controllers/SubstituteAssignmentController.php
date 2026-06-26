<?php

namespace App\Http\Controllers;

use App\Models\SubstituteAssignment;
use App\Models\TimetableSlot;
use App\Models\TeacherLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubstituteAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->query('date', now()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);
        $dayOfWeek = $date->dayOfWeekIso; // 1 = Mon, 7 = Sun

        // Find teachers on leave today
        $teachersOnLeave = TeacherLeave::where('date_from', '<=', $dateStr)
            ->where('date_to', '>=', $dateStr)
            ->pluck('teacher_id');

        // Find timetable slots for those teachers today
        $missingSlots = [];
        if ($teachersOnLeave->isNotEmpty() && $dayOfWeek <= 6) {
            $missingSlots = TimetableSlot::with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass', 'period', 'room'])
                ->where('day_of_week', $dayOfWeek)
                ->whereHas('teachingAssignment', function ($q) use ($teachersOnLeave) {
                    $q->whereIn('teacher_id', $teachersOnLeave);
                })
                ->get()
                ->map(function ($slot) use ($dateStr) {
                    // Check if substitute already assigned
                    $sub = SubstituteAssignment::with('substituteTeacher')
                        ->where('timetable_slot_id', $slot->id)
                        ->where('date', $dateStr)
                        ->first();
                    
                    $slot->substitute = $sub;
                    return $slot;
                });
        }

        return Inertia::render('SubstituteAssignment/Index', [
            'selectedDate' => $dateStr,
            'missingSlots' => $missingSlots,
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'timetable_slot_id' => 'required|exists:timetable_slots,id',
            'substitute_teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
        ]);

        // Check if exists
        $existing = SubstituteAssignment::where('timetable_slot_id', $validated['timetable_slot_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existing) {
            $existing->update(['substitute_teacher_id' => $validated['substitute_teacher_id']]);
        } else {
            $validated['id'] = (string) Str::uuid();
            SubstituteAssignment::create($validated);
        }

        return redirect()->back()->with('success', 'Substitute assigned successfully.');
    }
}
