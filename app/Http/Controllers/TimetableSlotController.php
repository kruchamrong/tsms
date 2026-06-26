<?php

namespace App\Http\Controllers;

use App\Models\TimetableSlot;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\TimetableEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableSlotController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->query('type', 'class'); // class or teacher
        $filterId = $request->query('id');

        $slots = [];
        if ($filterId) {
            $query = TimetableSlot::with(['teachingAssignment.subject', 'teachingAssignment.teacher', 'teachingAssignment.schoolClass', 'period', 'room']);
            
            if ($filterType === 'class') {
                $query->whereHas('teachingAssignment', function($q) use ($filterId) {
                    $q->where('school_class_id', $filterId);
                });
            } else {
                $query->whereHas('teachingAssignment', function($q) use ($filterId) {
                    $q->where('teacher_id', $filterId);
                });
            }
            $slots = $query->get();
        }

        return Inertia::render('Timetable/Index', [
            'classes' => SchoolClass::all(),
            'teachers' => Teacher::all(),
            'slots' => $slots,
            'filterType' => $filterType,
            'filterId' => $filterId,
        ]);
    }

    public function generate()
    {
        $engine = new TimetableEngine();
        $result = $engine->generate();

        return redirect()->route('timetables.index')->with($result['status'], $result['message']);
    }
}
