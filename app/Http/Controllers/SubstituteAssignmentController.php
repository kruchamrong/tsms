<?php

namespace App\Http\Controllers;

use App\Models\SubstituteAssignment;
use App\Models\TimetableSlot;
use App\Models\TeacherLeave;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SubstituteAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);
        $dateStr = $request->query('date', now()->format('Y-m-d'));
        
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $date = now();
            $dateStr = $date->format('Y-m-d');
        }
        $dayOfWeek = $date->dayOfWeekIso; // 1 = Mon, 7 = Sun

        // Find teachers on leave today
        $teachersOnLeave = TeacherLeave::whereDate('date_from', '<=', $dateStr)
            ->whereDate('date_to', '>=', $dateStr)
            ->pluck('teacher_id');

        // Find timetable slots for those teachers today
        $missingSlots = collect();
        if ($teachersOnLeave->isNotEmpty()) {
            $slots = TimetableSlot::with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass', 'period', 'room'])
                ->where('day_of_week', $dayOfWeek)
                ->whereHas('teachingAssignment', function ($q) use ($teachersOnLeave) {
                    $q->whereIn('teacher_id', $teachersOnLeave);
                })
                ->get();
                
            $slotIds = $slots->pluck('id');
            $substitutes = SubstituteAssignment::with('substituteTeacher')
                ->whereIn('timetable_slot_id', $slotIds)
                ->where('date', $dateStr)
                ->get()
                ->keyBy('timetable_slot_id');

            $missingSlots = $slots->map(function ($slot) use ($substitutes) {
                $slot->substitute = $substitutes->get($slot->id);
                return $slot;
            });
        }

        return Inertia::render('SubstituteAssignment/Index', [
            'selectedDate' => $dateStr,
            'missingSlots' => $missingSlots,
            'teachers' => Teacher::whereNotIn('id', $teachersOnLeave)->orderBy('khmer_name')->get(),
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
            'timetable_slot_id' => ['required', Rule::exists('timetable_slots', 'id')->where($schoolIdCheck)],
            'substitute_teacher_id' => [
                'required', 
                Rule::exists('teachers', 'id')->where($schoolIdCheck)->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($request) {
                    $onLeave = TeacherLeave::where('teacher_id', $value)
                        ->whereDate('date_from', '<=', $request->date)
                        ->whereDate('date_to', '>=', $request->date)
                        ->exists();
                    if ($onLeave) {
                        $fail('គ្រូបង្រៀននេះកំពុងសុំច្បាប់នៅថ្ងៃនេះ។');
                    }

                    $slot = TimetableSlot::find($request->timetable_slot_id);
                    if ($slot) {
                        $hasRegularClass = TimetableSlot::where('day_of_week', $slot->day_of_week)
                            ->where('period_id', $slot->period_id)
                            ->whereHas('teachingAssignment', function ($q) use ($value) {
                                $q->where('teacher_id', $value);
                            })
                            ->exists();
                        
                        $isSubstituting = SubstituteAssignment::where('date', $request->date)
                            ->where('substitute_teacher_id', $value)
                            ->whereHas('slot', function ($q) use ($slot) {
                                $q->where('period_id', $slot->period_id);
                            })
                            ->where('timetable_slot_id', '!=', $slot->id)
                            ->exists();

                        if ($hasRegularClass || $isSubstituting) {
                            $fail('គ្រូបង្រៀននេះជាប់ម៉ោងបង្រៀនរួចហើយនៅពេលនេះ។');
                        }
                    }
                }
            ],
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

        // Notify admins of the school where the slot belongs
        $slot = TimetableSlot::find($validated['timetable_slot_id']);
        $targetSchoolId = $slot ? $slot->school_id : auth()->user()->school_id;
        
        $admins = User::where(function ($query) use ($targetSchoolId) {
            if ($targetSchoolId) {
                $query->where('school_id', $targetSchoolId);
            } else {
                $query->whereNull('school_id');
            }
        })->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new SystemAlert('ការចាត់តាំងគ្រូជំនួស', 'គ្រូជំនួសត្រូវបានចាត់តាំងដោយជោគជ័យ។'));
        }

        return redirect()->back()->with('success', 'Substitute assigned successfully.');
    }
}
