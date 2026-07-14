<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherLeave;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');
        $user = auth()->user();
        
        if ($user->role === 'super_admin') {
            $stats = [
                'total_schools' => \App\Models\School::count(),
                'total_users' => \App\Models\User::where('role', 'school_admin')->count(),
                'total_subject_templates' => \App\Models\Subject::whereNull('school_id')->count(),
                'total_curricula_templates' => \App\Models\Curriculum::whereNull('school_id')->count(),
            ];
            
            $recentActivities = \OwenIt\Auditing\Models\Audit::with('user')->latest()->take(10)->get()->map(function ($audit) {
                return [
                    'id' => $audit->id,
                    'event' => $audit->event,
                    'log_name' => class_basename($audit->auditable_type),
                    'created_at' => $audit->created_at,
                ];
            });
            
            return Inertia::render('SuperAdmin/Dashboard', [
                'stats' => $stats,
                'recentActivities' => $recentActivities,
            ]);
        }

        $schoolId = $user->school_id;

        $totalSubjectsQuery = \DB::table('curriculum_subject')
            ->join('school_classes', 'school_classes.curriculum_id', '=', 'curriculum_subject.curriculum_id')
            ->distinct('curriculum_subject.subject_id');
            
        if ($schoolId) {
            $totalSubjectsQuery->where('school_classes.school_id', $schoolId);
        }
        
        $totalSubjects = $totalSubjectsQuery->count('curriculum_subject.subject_id');

        $stats = [
            'total_teachers' => Teacher::count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => $totalSubjects,
            'teachers_on_leave_today' => TeacherLeave::where('date_from', '<=', $today)
                ->where('date_to', '>=', $today)
                ->count(),
        ];

        // Let's generate a mock workload chart data: Top 5 teachers by weekly_hours
        // Actually, we can sum weekly_hours from TeachingAssignment group by teacher_id
        $workloadsQuery = \DB::table('teaching_assignments')
            ->join('teachers', 'teaching_assignments.teacher_id', '=', 'teachers.id')
            ->select(
                'teachers.id',
                'teachers.khmer_name',
                'teachers.english_name',
                'teachers.gender',
                'teachers.photo',
                'teachers.phone',
                \DB::raw('SUM(teaching_assignments.weekly_hours) as total_hours'),
                \DB::raw('COUNT(DISTINCT teaching_assignments.school_class_id) as total_classes')
            )
            ->groupBy('teachers.id', 'teachers.khmer_name', 'teachers.english_name', 'teachers.gender', 'teachers.photo', 'teachers.phone')
            ->orderByDesc('total_hours')
            ->limit(12);

        if ($schoolId) {
            $workloadsQuery->where('teaching_assignments.school_id', $schoolId);
        }
        
        $workloads = $workloadsQuery->get();

        // Calculate Alerts
        $classesQuery = SchoolClass::with('curriculum.subjects');
        $assignmentsQuery = \DB::table('teaching_assignments');
        
        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
            $assignmentsQuery->where('school_id', $schoolId);
        }
        
        $classes = $classesQuery->get();
        $assignments = $assignmentsQuery->get()->groupBy('school_class_id');
        $alerts = [];
        
        foreach ($classes as $schoolClass) {
            if ($schoolClass->curriculum && $schoolClass->curriculum->subjects) {
                $classAssigns = $assignments->get($schoolClass->id) ?: collect();
                foreach ($schoolClass->curriculum->subjects as $subject) {
                    // Only alert if the subject actually has teaching hours in the curriculum
                    if ($subject->pivot->weekly_hours > 0) {
                        if (!$classAssigns->where('subject_id', $subject->id)->count()) {
                            $alerts[] = [
                                'type' => 'missing_teacher',
                                'message' => "ថ្នាក់ {$schoolClass->class_code} អត់មានគ្រូបង្រៀនមុខវិជ្ជា {$subject->khmer_name}"
                            ];
                        }
                    }
                }
            } else {
                 $alerts[] = [
                    'type' => 'missing_curriculum',
                    'message' => "ថ្នាក់ {$schoolClass->class_code} មិនទាន់មានកម្មវិធីសិក្សា"
                 ];
            }
        }

        foreach ($workloads as $wl) {
            if ($wl->total_hours < 10) {
                $alerts[] = [
                    'type' => 'underloaded_teacher',
                    'message' => "គ្រូ {$wl->khmer_name} មានម៉ោងបង្រៀនតែ {$wl->total_hours} ម៉ោង"
                ];
            }
        }

        // Live Overview (Active Classes right now)
        $currentTime = now()->format('H:i:s');
        $currentDay = now()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        
        $currentPeriod = \App\Models\Period::where('school_id', $schoolId)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();

        $activeClasses = [];
        if ($currentPeriod && $currentDay <= 6) {
            $slots = \App\Models\TimetableSlot::with(['teachingAssignment.schoolClass.room', 'teachingAssignment.teacher', 'teachingAssignment.subject'])
                ->where('period_id', $currentPeriod->id)
                ->where('day_of_week', $currentDay)
                ->whereHas('teachingAssignment', function($q) use ($schoolId) {
                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })
                ->get();
                
            foreach ($slots as $slot) {
                $assignment = $slot->teachingAssignment;
                if ($assignment && $assignment->schoolClass) {
                    $activeClasses[] = [
                        'class_code' => $assignment->schoolClass->class_code,
                        'teacher_name' => $assignment->teacher->khmer_name ?? $assignment->teacher->english_name,
                        'subject_name' => $assignment->subject->khmer_name,
                        'room' => $assignment->schoolClass->room->name ?? 'មិនបញ្ជាក់',
                    ];
                }
            }
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'workloads' => $workloads,
            'alerts' => array_slice($alerts, 0, 10), // Send max 10 alerts
            'totalAlerts' => count($alerts),
            'activeClasses' => $activeClasses,
            'currentPeriod' => $currentPeriod ? $currentPeriod->start_time . ' - ' . $currentPeriod->end_time : null
        ]);
    }
}
