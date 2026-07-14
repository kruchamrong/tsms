<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Shift;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class TeachingAssignmentService
{
    public function getAssignmentData(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $subjectId = $request->query('subject_id');
        $gradeId = $request->query('grade_id');
        $shiftId = $request->query('shift_id');

        // First, build a base query for assignments to compute global metrics
        $assignmentQuery = TeachingAssignment::query()
            ->join('teachers', 'teaching_assignments.teacher_id', '=', 'teachers.id')
            ->join('subjects', 'teaching_assignments.subject_id', '=', 'subjects.id')
            ->join('school_classes', 'teaching_assignments.school_class_id', '=', 'school_classes.id')
            ->when($teacherId, function ($q) use ($teacherId) {
                return $q->where('teaching_assignments.teacher_id', $teacherId);
            })
            ->when($subjectId, function ($q) use ($subjectId) {
                return $q->where('teaching_assignments.subject_id', $subjectId);
            })
            ->when($gradeId, function ($q) use ($gradeId) {
                return $q->where('school_classes.grade_id', $gradeId);
            })
            ->when($shiftId, function ($q) use ($shiftId) {
                return $q->where('teaching_assignments.shift_id', $shiftId);
            });

        $totalAssignments = $assignmentQuery->count();
        $totalHours = $assignmentQuery->sum('teaching_assignments.weekly_hours');
        $uniqueTeachersCount = $assignmentQuery->distinct('teaching_assignments.teacher_id')->count('teaching_assignments.teacher_id');

        $hasFilters = $teacherId || $subjectId || $gradeId || $shiftId;

        $teachers = collect();
        
        if ($hasFilters) {
            $teachers = Teacher::query()
                ->when($teacherId, function ($q) use ($teacherId) {
                    return $q->where('id', $teacherId);
                })
                ->whereHas('teachingAssignments', function ($q) use ($gradeId, $shiftId, $subjectId) {
                    if ($gradeId) {
                        $q->whereHas('schoolClass', function ($sq) use ($gradeId) {
                            $sq->where('grade_id', $gradeId);
                        });
                    }
                    if ($shiftId) {
                        $q->where('shift_id', $shiftId);
                    }
                    if ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }
                })
                ->with(['teachingAssignments' => function ($q) use ($gradeId, $shiftId, $subjectId) {
                    $q->with(['subject', 'schoolClass.grade', 'shift']);
                    if ($gradeId) {
                        $q->whereHas('schoolClass', function ($sq) use ($gradeId) {
                            $sq->where('grade_id', $gradeId);
                        });
                    }
                    if ($shiftId) {
                        $q->where('shift_id', $shiftId);
                    }
                    if ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }
                }])
                ->orderBy('khmer_name')
                ->get();
        }

        $groupedData = [];
        $subjectCounters = [];

        foreach ($teachers as $teacher) {
            $groupedBySubject = $teacher->teachingAssignments->groupBy('subject_id');
            foreach ($groupedBySubject as $subjId => $assignmentsGroup) {
                $subject = $assignmentsGroup->first()->subject;
                
                if (!isset($subjectCounters[$subjId])) {
                    $subjectCounters[$subjId] = 1;
                } else {
                    $subjectCounters[$subjId]++;
                }
                
                $shortName = $subject->short_name ?: ($subject->english_name ? substr($subject->english_name, 0, 1) : 'S');
                $subjectCode = $shortName . $subjectCounters[$subjId];

                $groupedData[] = [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->khmer_name,
                    'teacher_gender' => $teacher->gender,
                    'teacher_english' => $teacher->english_name,
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->khmer_name,
                    'subject_code' => $subjectCode,
                    'subject_color' => $subject->color,
                    'real_subject_code' => $subject->subject_code,
                    'assignments' => $assignmentsGroup->map(function($a) {
                        return [
                            'id' => $a->id,
                            'class_code' => $a->schoolClass->class_code,
                            'grade_name' => $a->schoolClass->grade->name ?? '',
                            'weekly_hours' => $a->weekly_hours,
                            'shift_name' => $a->shift ? $a->shift->name : '',
                        ];
                    })->sort(function($a, $b) {
                        preg_match('/^(\d+)/', $a['class_code'], $mA);
                        preg_match('/^(\d+)/', $b['class_code'], $mB);
                        $gradeA = intval($mA[1] ?? 0);
                        $gradeB = intval($mB[1] ?? 0);
                        if ($gradeA !== $gradeB) {
                            return $gradeB <=> $gradeA; 
                        }
                        return strcmp($a['class_code'], $b['class_code']);
                    })->values()->all()
                ];
            }
        }

        usort($groupedData, function ($a, $b) {
            $cmpSubject = $a['real_subject_code'] <=> $b['real_subject_code'];
            if ($cmpSubject === 0) {
                preg_match('/\d+/', $a['subject_code'], $numA);
                preg_match('/\d+/', $b['subject_code'], $numB);
                return (int)($numA[0] ?? 0) <=> (int)($numB[0] ?? 0);
            }
            return $cmpSubject;
        });

        $perPage = 20;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($groupedData, $offset, $perPage);
        $paginatedResponse = new LengthAwarePaginator(
            $paginatedItems, 
            count($groupedData), 
            $perPage, 
            $page, 
            ['path' => request()->url(), 'query' => request()->query()]
        );
            
        return [
            'assignments' => $paginatedResponse,
            'has_filters' => (bool)$hasFilters,
            'summary' => [
                'total_assignments' => $totalAssignments,
                'total_hours' => $totalHours,
                'total_teachers' => $uniqueTeachersCount,
            ],
            'filters' => [
                'subject_id' => $subjectId,
                'grade_id' => $gradeId,
                'shift_id' => $shiftId,
            ],
            'teachers' => Teacher::orderBy('khmer_name')->get(),
            'subjects' => Subject::all(),
            'grades' => Grade::orderBy('id', 'desc')->get(),
            'classes' => SchoolClass::with('grade')->orderBy('grade_id', 'desc')->orderBy('class_code')->get(),
            'shifts' => Shift::all(),
            'selectedTeacherId' => $teacherId,
            'selectedSubjectId' => $subjectId,
            'curriculumSubjects' => DB::table('curriculum_subject')->get(),
            'takenAssignments' => TeachingAssignment::select('subject_id', 'school_class_id')->get(),
        ];
    }

    public function storeAssignment(array $validated)
    {
        $subjectId = $validated['subject_id'];

        \App\Models\TeachingAssignment::disableAuditing();
        DB::transaction(function () use ($validated, $subjectId) {
            foreach ($validated['school_class_ids'] as $classId) {
                $weeklyHours = null;
                $schoolClass = SchoolClass::find($classId);

                if ($schoolClass) {
                    $curriculumSubject = DB::table('curriculum_subject')
                        ->where('curriculum_id', $schoolClass->curriculum_id)
                        ->where('subject_id', $subjectId)
                        ->first();
                    
                    $weeklyHours = $curriculumSubject ? $curriculumSubject->weekly_hours : 0;
                }

                if ($weeklyHours > 0) {
                    TeachingAssignment::updateOrCreate(
                        [
                            'teacher_id' => $validated['teacher_id'],
                            'subject_id' => $subjectId,
                            'school_class_id' => $classId,
                            'shift_id' => $schoolClass ? ($schoolClass->shift_id ?? 1) : 1,
                        ],
                        [
                            'weekly_hours' => $weeklyHours,
                        ]
                    );
                }
            }
        });
        \App\Models\TeachingAssignment::enableAuditing();
    }

    public function checkClassStatus($classId)
    {
        $schoolClass = SchoolClass::with('curriculum.subjects')->find($classId);

        if (!$schoolClass) {
            return ['error' => 'Class not found', 'status' => 404];
        }

        if (!$schoolClass->curriculum) {
            return ['error' => 'ថ្នាក់នេះមិនទាន់បានកំណត់កម្មវិធីសិក្សានៅឡើយទេ។ សូមចូលទៅកាន់ផ្ទាំងថ្នាក់រៀន ដើម្បីកំណត់កម្មវិធីសិក្សាឲ្យថ្នាក់នេះសិន។', 'status' => 400];
        }

        $curriculumSubjects = $schoolClass->curriculum->subjects;
        $assignments = TeachingAssignment::with(['teacher', 'subject'])
            ->where('school_class_id', $classId)
            ->get();

        $result = [];
        
        foreach ($curriculumSubjects as $subject) {
            $requiredHours = $subject->pivot->weekly_hours;
            $subjectAssignments = $assignments->where('subject_id', $subject->id);
            
            $assignedHours = $subjectAssignments->sum('weekly_hours');
            $teachers = $subjectAssignments->map(function ($a) {
                return $a->teacher->khmer_name;
            })->unique()->values();

            $status = 'missing';
            if ($assignedHours >= $requiredHours) {
                $status = 'full';
            } elseif ($assignedHours > 0) {
                $status = 'partial';
            }

            $result[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->khmer_name,
                'required_hours' => $requiredHours,
                'assigned_hours' => $assignedHours,
                'teachers' => $teachers,
                'status' => $status
            ];
        }

        $assignedSubjectIds = $assignments->pluck('subject_id')->unique();
        $curriculumSubjectIds = $curriculumSubjects->pluck('id');
        $extraSubjectIds = $assignedSubjectIds->diff($curriculumSubjectIds);
        
        foreach ($extraSubjectIds as $extraId) {
            $subjectAssignments = $assignments->where('subject_id', $extraId);
            $subject = $subjectAssignments->first()->subject;
            $assignedHours = $subjectAssignments->sum('weekly_hours');
            $teachers = $subjectAssignments->map(function ($a) {
                return $a->teacher->khmer_name;
            })->unique()->values();
            
            $result[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->khmer_name,
                'required_hours' => 0,
                'assigned_hours' => $assignedHours,
                'teachers' => $teachers,
                'status' => 'extra'
            ];
        }

        return [
            'data' => [
                'class_name' => $schoolClass->class_code,
                'subjects' => $result
            ],
            'status' => 200
        ];
    }

    public function reassignAssignment(array $validated)
    {
        \App\Models\TeachingAssignment::disableAuditing();
        DB::transaction(function () use ($validated) {
            $assignment = TeachingAssignment::findOrFail($validated['assignment_id']);
            
            $existing = TeachingAssignment::where('teacher_id', $validated['new_teacher_id'])
                ->where('subject_id', $validated['new_subject_id'])
                ->where('school_class_id', $assignment->school_class_id)
                ->where('shift_id', $assignment->shift_id)
                ->where('id', '!=', $assignment->id)
                ->first();

            if ($existing) {
                $assignment->delete();
            } else {
                $assignment->teacher_id = $validated['new_teacher_id'];
                $assignment->subject_id = $validated['new_subject_id'];
                $assignment->save();
            }
        });
        \App\Models\TeachingAssignment::enableAuditing();
    }

    public function destroyGroup(Teacher $teacher, Subject $subject)
    {
        \App\Models\TeachingAssignment::disableAuditing();
        TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->delete();
        \App\Models\TeachingAssignment::enableAuditing();
    }

    public function truncateAssignments()
    {
        if (auth()->check() && auth()->user()->school_id) {
            TeachingAssignment::where('school_id', auth()->user()->school_id)->delete();
        } else {
            TeachingAssignment::whereNull('school_id')->delete();
        }
    }
}

