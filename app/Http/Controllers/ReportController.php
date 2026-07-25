<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\TeachingAssignment;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function teacherWorkloads(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $workloads = DB::table('teaching_assignments')
            ->where('teaching_assignments.school_id', $schoolId)
            ->join('teachers', 'teaching_assignments.teacher_id', '=', 'teachers.id')
            ->select(
                'teachers.id',
                'teachers.khmer_name',
                'teachers.english_name',
                'teachers.gender',
                'teachers.phone',
                DB::raw('SUM(teaching_assignments.weekly_hours) as total_hours'),
                DB::raw('COUNT(DISTINCT teaching_assignments.school_class_id) as total_classes')
            )
            ->groupBy('teachers.id', 'teachers.khmer_name', 'teachers.english_name', 'teachers.gender', 'teachers.phone')
            ->orderBy('teachers.khmer_name')
            ->get();

        $assignments = DB::table('teaching_assignments')
            ->where('teaching_assignments.school_id', $schoolId)
            ->join('school_classes', 'teaching_assignments.school_class_id', '=', 'school_classes.id')
            ->join('subjects', 'teaching_assignments.subject_id', '=', 'subjects.id')
            ->select(
                'teaching_assignments.teacher_id',
                'school_classes.class_code as class_name',
                'subjects.khmer_name as subject_name',
                'teaching_assignments.weekly_hours'
            )
            ->get();
            
        $assignmentsByTeacher = $assignments->groupBy('teacher_id');
        
        $workloads = $workloads->map(function ($workload) use ($assignmentsByTeacher) {
            $workload->assignments = $assignmentsByTeacher->get($workload->id) ?: [];
            return $workload;
        });

        return Inertia::render('Reports/TeacherWorkload', [
            'workloads' => $workloads,
            'school' => auth()->user()->school
        ]);
    }

    public function classAssignments(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)
            ->with('curriculum.subjects')
            ->orderBy('grade_id', 'desc')
            ->orderBy('class_code', 'asc')
            ->get();
        
        $assignments = DB::table('teaching_assignments')
            ->where('teaching_assignments.school_id', $schoolId)
            ->join('teachers', 'teaching_assignments.teacher_id', '=', 'teachers.id')
            ->join('subjects', 'teaching_assignments.subject_id', '=', 'subjects.id')
            ->select(
                'teaching_assignments.school_class_id',
                'teaching_assignments.subject_id',
                'teachers.khmer_name as teacher_name',
                'teachers.phone as teacher_phone',
                'subjects.khmer_name as subject_name',
                'teaching_assignments.weekly_hours'
            )
            ->get();

        $assignmentsByClass = $assignments->groupBy('school_class_id');
        
        $classData = $classes->map(function ($schoolClass) use ($assignmentsByClass) {
            $classAssignments = $assignmentsByClass->get($schoolClass->id) ?: collect();
            $mappedAssignments = [];
            
            if ($schoolClass->curriculum && $schoolClass->curriculum->subjects) {
                $curriculumSubjectIds = $schoolClass->curriculum->subjects->pluck('id')->toArray();
                
                foreach ($schoolClass->curriculum->subjects as $subject) {
                    $subjectAssignments = $classAssignments->where('subject_id', $subject->id);
                    if ($subjectAssignments->count() > 0) {
                        foreach ($subjectAssignments as $assign) {
                            $mappedAssignments[] = $assign;
                        }
                    } else {
                        $mappedAssignments[] = (object)[
                            'subject_id' => $subject->id,
                            'subject_name' => $subject->khmer_name,
                            'teacher_name' => '', // Blank for UI to handle or show as "មិនទាន់មានគ្រូ"
                            'teacher_phone' => '',
                            'weekly_hours' => $subject->pivot->weekly_hours
                        ];
                    }
                }
                
                // Append any extra assignments not in curriculum
                $extraAssignments = $classAssignments->whereNotIn('subject_id', $curriculumSubjectIds);
                foreach ($extraAssignments as $assign) {
                    $mappedAssignments[] = $assign;
                }
            } else {
                $mappedAssignments = $classAssignments->toArray();
            }

            return [
                'id' => $schoolClass->id,
                'name' => $schoolClass->class_code,
                'assignments' => $mappedAssignments
            ];
        });

        return Inertia::render('Reports/ClassAssignment', [
            'classes' => $classData,
            'school' => auth()->user()->school
        ]);
    }

    public function teacherTimetables(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $teachers = Teacher::where('school_id', $schoolId)
            ->with([
                'teachingAssignments.subject', 
                'teachingAssignments.schoolClass', 
                'teachingAssignments.timetableSlots.period',
                'homeroom_classes'
            ])
            ->orderBy('khmer_name')
            ->get();
            
        $periods = \App\Models\Period::orderBy('start_time')->get();
        
        return Inertia::render('Reports/TeacherTimetable', [
            'teachers' => $teachers,
            'periods' => $periods,
            'school' => auth()->user()->school
        ]);
    }

    public function classTimetables(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)
            ->with([
                'teachingAssignments.subject', 
                'teachingAssignments.teacher', 
                'teachingAssignments.timetableSlots.period',
                'homeroomTeacher',
                'room'
            ])
            ->orderByDesc('grade_id')
            ->orderBy('class_code')
            ->get();
            
        $periods = \App\Models\Period::orderBy('start_time')->get();
        
        return Inertia::render('Reports/ClassTimetable', [
            'classes' => $classes,
            'periods' => $periods,
            'school' => auth()->user()->school
        ]);
    }

    public function masterTimetables(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        
        $shifts = \App\Models\Shift::all();
        $shiftId = $request->query('shift_id', $shifts->first()->id ?? 1);
        $levelId = $request->query('level_id');

        $classesQuery = \App\Models\SchoolClass::query()->where('school_id', $schoolId);

        if ($levelId === 'lower') {
            $classesQuery->whereIn('grade_id', [1, 2, 3]); // Grades 7-9
        } elseif ($levelId === 'upper') {
            $classesQuery->whereIn('grade_id', [4, 5, 6]); // Grades 10-12
        }

        if ($shiftId !== 'all') {
            $classesQuery->where('shift_id', $shiftId);
        }

        $classes = $classesQuery->orderBy('grade_id')
            ->orderBy('class_code')
            ->get();
            
        $periodsQuery = \App\Models\Period::query();
        if ($shiftId !== 'all') {
            $periodsQuery->where('shift_id', $shiftId);
        }
        $periods = $periodsQuery->orderBy('start_time')->get();
        
        $classIds = $classes->pluck('id');

        $slots = \App\Models\TimetableSlot::with(['teachingAssignment.subject', 'teachingAssignment.teacher'])
            ->whereHas('teachingAssignment', function($q) use ($classIds) {
                $q->whereIn('school_class_id', $classIds);
            })->get();

        // Calculate K1, CM1 logic
        $allTeachers = \App\Models\Teacher::where('school_id', $schoolId)->with('teachingAssignments.subject')->orderBy('khmer_name')->get();
        $subjectCounters = [];
        $teacherSubjectCodes = [];

        foreach ($allTeachers as $teacher) {
            $groupedBySubject = $teacher->teachingAssignments->groupBy('subject_id');
            foreach ($groupedBySubject as $subjId => $assignmentsGroup) {
                if (!isset($subjectCounters[$subjId])) {
                    $subjectCounters[$subjId] = 1;
                } else {
                    $subjectCounters[$subjId]++;
                }
                $subject = $assignmentsGroup->first()->subject;
                $shortName = $subject->short_name ?: ($subject->english_name ? substr($subject->english_name, 0, 1) : 'S');
                $teacherSubjectCodes[$teacher->id . '_' . $subjId] = $shortName . $subjectCounters[$subjId];
            }
        }
        
        return Inertia::render('Reports/MasterTimetable', [
            'school' => auth()->user()->school,
            'classes' => $classes,
            'periods' => $periods,
            'slots' => $slots,
            'teacherSubjectCodes' => $teacherSubjectCodes,
            'shifts' => $shifts,
            'filters' => [
                'level_id' => $levelId,
                'shift_id' => $shiftId,
            ]
        ]);
    }

    public function homeroomTeachers(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $query = SchoolClass::where('school_id', $schoolId)
            ->whereNotNull('homeroom_teacher_id')
            ->with(['homeroomTeacher', 'grade', 'room', 'shift']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('class_code', 'like', "%{$search}%")
                  ->orWhereHas('homeroomTeacher', function($q2) use ($search) {
                      $q2->where('khmer_name', 'like', "%{$search}%")
                         ->orWhere('english_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        $classes = $query->orderBy('grade_id', 'desc')->orderBy('class_code')->get();

        return Inertia::render('Reports/HomeroomTeacher', [
            'classes' => $classes,
            'grades' => \App\Models\Grade::orderBy('id', 'desc')->get(),
            'shifts' => \App\Models\Shift::all(),
            'filters' => $request->only(['search', 'grade_id', 'shift_id']),
            'school' => auth()->user()->school
        ]);
    }
}
