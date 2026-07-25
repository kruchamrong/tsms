<?php

namespace App\Services;

use App\Models\TimetableSlot;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Shift;
use App\Models\Period;
use App\Models\TeachingAssignment;
use App\Models\TeacherAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TimetableService
{
    public function getTimetableData(Request $request)
    {
        $shifts = Cache::remember('timetable_shifts', 300, fn() => Shift::all());
        $shiftId = $request->query('shift_id', $shifts->first()->id ?? 1);
        $teacherId = $request->query('teacher_id');
        $levelId = $request->query('level_id');

        $cacheKey = "timetable_base_data_{$shiftId}_{$teacherId}_{$levelId}";
        $baseData = Cache::remember($cacheKey, 300, function () use ($shiftId, $teacherId, $levelId) {
            $classesQuery = SchoolClass::with('curriculum.subjects');

            if ($levelId === 'lower') {
                $classesQuery->whereIn('grade_id', [1, 2, 3]); // Grades 7-9
            } elseif ($levelId === 'upper') {
                $classesQuery->whereIn('grade_id', [4, 5, 6]); // Grades 10-12
            }

            if ($teacherId) {
                $classesQuery->whereHas('teachingAssignments', function($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                });
            } else {
                if ($shiftId !== 'all') {
                    $classesQuery->where('shift_id', $shiftId);
                }
            }

            $classes = $classesQuery->orderBy('grade_id')
                ->orderBy('class_code')
                ->get();
                
            $periods = Period::orderBy('start_time')->get();
            $classIds = $classes->pluck('id');

            $assignmentsQuery = TeachingAssignment::with(['subject', 'schoolClass', 'teacher']);
            $assignmentsQuery->where(function($q) use ($classIds, $teacherId) {
                $q->whereIn('school_class_id', $classIds);
                if ($teacherId) {
                    $q->orWhere('teacher_id', $teacherId);
                }
            });
            $assignments = $assignmentsQuery->get();

            $allTeachers = Teacher::with('teachingAssignments.subject')->orderBy('khmer_name')->get();
            $subjects = \App\Models\Subject::all();
            
            return compact('classes', 'classIds', 'periods', 'assignments', 'allTeachers', 'subjects');
        });

        $classes = $baseData['classes'];
        $classIds = $baseData['classIds'];
        $periods = $baseData['periods'];
        $assignments = $baseData['assignments'];
        $allTeachers = $baseData['allTeachers'];
        $subjects = $baseData['subjects'];

        $slotsQuery = TimetableSlot::with(['teachingAssignment.subject', 'teachingAssignment.teacher', 'room']);
        $slotsQuery->whereHas('teachingAssignment', function($q) use ($classIds, $teacherId) {
            $q->whereIn('school_class_id', $classIds);
            if ($teacherId) {
                $q->orWhere('teacher_id', $teacherId);
            }
        });
        $slots = $slotsQuery->get();

        $assignmentsIds = $assignments->pluck('id');
        $assignmentsSlotsCount = TimetableSlot::whereIn('teaching_assignment_id', $assignmentsIds)
            ->selectRaw('teaching_assignment_id, count(*) as count')
            ->groupBy('teaching_assignment_id')
            ->pluck('count', 'teaching_assignment_id');
            
        $assignments->each(function($assignment) use ($assignmentsSlotsCount) {
            $assignment->assigned_hours = $assignmentsSlotsCount->get($assignment->id, 0);
        });

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

        $allClassAssignments = $assignments->whereIn('school_class_id', $classIds);
        $allClassSlotsCount = $assignmentsSlotsCount;

        $classStats = [];
        $classAssignmentsDetail = [];
        
        foreach ($classIds as $cId) {
            $class = $classes->firstWhere('id', $cId);
            $assignmentsForClass = $allClassAssignments->where('school_class_id', $cId);
            
            // Use the class's curriculum for the required hours if available
            if ($class && $class->curriculum) {
                $totalRequired = $class->curriculum->subjects->sum('pivot.weekly_hours');
            } else {
                $totalRequired = $assignmentsForClass->sum('weekly_hours');
            }
            
            $totalAssigned = 0;
            foreach ($assignmentsForClass as $assignment) {
                $totalAssigned += $allClassSlotsCount->get($assignment->id, 0);
            }
            $classStats[$cId] = [
                'required' => $totalRequired,
                'assigned' => $totalAssigned
            ];
            
            $classAssignmentsDetail[$cId] = $assignmentsForClass->map(function($a) use ($allClassSlotsCount) {
                return [
                    'id' => $a->id,
                    'subject_name' => $a->subject->khmer_name,
                    'subject_code' => $a->subject->short_name,
                    'subject_color' => $a->subject->color,
                    'teacher_name' => $a->teacher->khmer_name,
                    'teacher_id' => $a->teacher_id,
                    'subject_id' => $a->subject_id,
                    'weekly_hours' => $a->weekly_hours,
                    'assigned_hours' => $allClassSlotsCount->get($a->id, 0),
                ];
            })->values()->all();
        }

        $teacherWorkloadsMap = [];
        foreach ($allClassAssignments as $a) {
            if (!isset($teacherWorkloadsMap[$a->teacher_id])) {
                $teacherWorkloadsMap[$a->teacher_id] = [
                    'id' => $a->teacher_id,
                    'name' => $a->teacher->khmer_name,
                    'code' => $a->teacher->teacher_code,
                    'photo' => $a->teacher->photo,
                    'phone' => $a->teacher->phone,
                    'required' => 0,
                    'assigned' => 0,
                    'subjectsMap' => []
                ];
            }
            $teacherWorkloadsMap[$a->teacher_id]['required'] += $a->weekly_hours;
            $teacherWorkloadsMap[$a->teacher_id]['assigned'] += $allClassSlotsCount->get($a->id, 0);

            $subjectId = $a->subject_id;
            if (!isset($teacherWorkloadsMap[$a->teacher_id]['subjectsMap'][$subjectId])) {
                $teacherWorkloadsMap[$a->teacher_id]['subjectsMap'][$subjectId] = [
                    'name' => $a->subject->short_name ?: $a->subject->khmer_name,
                    'color' => $a->subject->color,
                    'classes' => []
                ];
            }
            $teacherWorkloadsMap[$a->teacher_id]['subjectsMap'][$subjectId]['classes'][] = [
                'name' => $a->schoolClass->class_code,
                'hours' => $a->weekly_hours
            ];
        }
        
        $teacherWorkloads = array_values($teacherWorkloadsMap);
        foreach ($teacherWorkloads as &$tw) {
            $tw['subjects'] = array_values($tw['subjectsMap']);
            unset($tw['subjectsMap']);
        }
        usort($teacherWorkloads, function($a, $b) {
            if ($a['required'] === $b['required']) {
                return $a['name'] <=> $b['name'];
            }
            return $b['required'] <=> $a['required'];
        });

        return [
            'shifts' => $shifts,
            'subjects' => $subjects,
            'classes' => $classes,
            'periods' => $periods,
            'teachers' => $allTeachers,
            'slots' => $slots,
            'assignments' => $assignments,
            'shiftId' => $shiftId,
            'teacherId' => $teacherId,
            'teacherSubjectCodes' => $teacherSubjectCodes,
            'classStats' => $classStats,
            'classAssignmentsDetail' => $classAssignmentsDetail,
            'teacherWorkloads' => $teacherWorkloads,
        ];
    }

    public function checkTeacherAvailability($teacherId, $dayOfWeek, $shiftId)
    {
        $hasAnyRecord = TeacherAvailability::where('teacher_id', $teacherId)->exists();
        if (!$hasAnyRecord) {
            return true;
        }

        $availability = TeacherAvailability::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('shift_id', $shiftId)
            ->first();

        return $availability ? $availability->is_available : false;
    }

    public function getHoursInShift($assignmentId, $dayOfWeek, $periodId)
    {
        $period = Period::find($periodId);
        if (!$period) return 0;

        return TimetableSlot::where('teaching_assignment_id', $assignmentId)
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('period', function($q) use ($period) {
                $q->where('shift_id', $period->shift_id);
            })
            ->count();
    }

    public function toggleSlot(array $validated)
    {
        $classId = $validated['school_class_id'];
        $dayOfWeek = $validated['day_of_week'];
        $periodId = $validated['period_id'];
        $teacherId = $validated['teacher_id'];
        $subjectId = $validated['subject_id'] ?? null;

        $schoolClass = SchoolClass::findOrFail($classId);

        $assignmentQuery = TeachingAssignment::where('teacher_id', $teacherId)
            ->where('school_class_id', $classId);
            
        if ($subjectId) {
            $assignmentQuery->where('subject_id', $subjectId);
        }
        
        $assignment = $assignmentQuery->first();

        if (!$assignment) {
            return ['type' => 'error', 'message' => 'គ្រូនេះមិនមានម៉ោងត្រូវបង្រៀនថ្នាក់នេះ សម្រាប់មុខវិជ្ជានេះទេ។'];
        }

        $existingSlotForClass = TimetableSlot::whereHas('teachingAssignment', function($q) use ($classId) {
            $q->where('school_class_id', $classId);
        })->where('day_of_week', $dayOfWeek)
          ->where('period_id', $periodId)
          ->first();

        if ($existingSlotForClass && $existingSlotForClass->teachingAssignment->teacher_id == $teacherId) {
            $existingSlotForClass->delete();
            return ['type' => 'success', 'message' => 'បានលុបម៉ោងបង្រៀនចេញ។'];
        }

        $isAvailable = $this->checkTeacherAvailability($teacherId, $dayOfWeek, $schoolClass->shift_id);

        if (!$isAvailable) {
            return ['type' => 'error', 'message' => 'គ្រូនេះមិនអាចបង្រៀននៅថ្ងៃដែលបានជ្រើសរើសបានទេ (ត្រូវបានកំណត់ម៉ោងទំនេរ)។'];
        }

        return DB::transaction(function () use ($teacherId, $classId, $dayOfWeek, $periodId, $subjectId, $schoolClass, $isAvailable) {
            // Re-fetch existing slot with lock to prevent concurrent modifications
            $existingSlotForClass = TimetableSlot::whereHas('teachingAssignment', function($q) use ($classId) {
                $q->where('school_class_id', $classId);
            })->where('day_of_week', $dayOfWeek)
              ->where('period_id', $periodId)
              ->lockForUpdate()
              ->first();

            $assignmentsQuery = TeachingAssignment::where('teacher_id', $teacherId)
                ->where('school_class_id', $classId)
                ->lockForUpdate();
                
            if ($subjectId) {
                $assignmentsQuery->where('subject_id', $subjectId);
            }
            
            $assignments = $assignmentsQuery->get();

            if ($assignments->isEmpty()) {
                return ['type' => 'error', 'message' => 'គ្រូនេះមិនមានម៉ោងបង្រៀននៅថ្នាក់នេះទេ។'];
            }

            $validAssignment = null;
            foreach ($assignments as $a) {
                $currentHours = TimetableSlot::where('teaching_assignment_id', $a->id)->count();
                if ($currentHours >= $a->weekly_hours) {
                    continue;
                }

                $hoursInShift = $this->getHoursInShift($a->id, $dayOfWeek, $periodId);
                if ($hoursInShift >= 2) {
                    continue;
                }

                $validAssignment = $a;
                break;
            }

            if (!$validAssignment) {
                if ($subjectId) {
                    return ['type' => 'error', 'message' => 'មិនអាចបញ្ចូលបានទេ! មុខវិជ្ជានេះបានបង្រៀនគ្រប់ម៉ោងប្រចាំសប្តាហ៍ ឬបានបង្រៀន ២ម៉ោង ពេញរួចហើយសម្រាប់ពេលនេះ (ព្រឹក/រសៀល)។'];
                }
                return ['type' => 'error', 'message' => 'មិនអាចបញ្ចូលបានទេ! មុខវិជ្ជាទាំងអស់របស់គ្រូនេះបានបង្រៀនគ្រប់ម៉ោងប្រចាំសប្តាហ៍ ឬបានបង្រៀន ២ម៉ោង ពេញរួចហើយសម្រាប់ពេលនេះ (ព្រឹក/រសៀល)។'];
            }

            $isTeacherBusy = TimetableSlot::whereHas('teachingAssignment', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->where('day_of_week', $dayOfWeek)
            ->where('period_id', $periodId)
            ->exists();

            if ($isTeacherBusy) {
                return ['type' => 'error', 'message' => 'គ្រូកំពុងជាប់បង្រៀនថ្នាក់ផ្សេងនៅម៉ោងនេះ។'];
            }

            if ($existingSlotForClass) {
                $existingSlotForClass->update([
                    'teaching_assignment_id' => $validAssignment->id,
                ]);
                return ['type' => 'success', 'message' => 'បានផ្លាស់ប្តូរគ្រូបង្រៀនដោយជោគជ័យ។'];
            }

            TimetableSlot::create([
                'id' => (string) Str::uuid(),
                'teaching_assignment_id' => $validAssignment->id,
                'period_id' => $periodId,
                'day_of_week' => $dayOfWeek,
                'room_id' => $schoolClass->room_id ?? \App\Models\Room::first()->id,
                'status' => 'Scheduled',
            ]);

            return ['type' => 'success', 'message' => 'បានបន្ថែមម៉ោងបង្រៀនដោយជោគជ័យ។'];
        });
    }

    public function swapSlot(array $validated)
    {
        $sourceSlot = TimetableSlot::with(['teachingAssignment.teacher', 'teachingAssignment.subject'])->findOrFail($validated['source_slot_id']);
        $sourceTeacherId = $sourceSlot->teachingAssignment->teacher_id;
        $sourceClassId = $sourceSlot->teachingAssignment->school_class_id;
        
        if ($sourceClassId != $validated['target_class_id']) {
            return ['type' => 'error', 'message' => 'មិនអាចទាញដូរទីតាំងឆ្លងថ្នាក់បានទេ!'];
        }

        $targetSlot = TimetableSlot::with(['teachingAssignment.teacher', 'teachingAssignment.subject'])
            ->whereHas('teachingAssignment', function($q) use ($validated) {
                $q->where('school_class_id', $validated['target_class_id']);
            })
            ->where('day_of_week', $validated['target_day_id'])
            ->where('period_id', $validated['target_period_id'])
            ->first();

        return DB::transaction(function () use ($sourceSlot, $targetSlot, $validated, $sourceTeacherId) {
            // Lock the slots for update
            $sourceSlot = TimetableSlot::lockForUpdate()->find($sourceSlot->id);
            if ($targetSlot) {
                $targetSlot = TimetableSlot::lockForUpdate()->find($targetSlot->id);
                
                if ($sourceSlot->id == $targetSlot->id) {
                    return ['type' => 'none'];
                }

                $targetTeacherId = $targetSlot->teachingAssignment->teacher_id;
                
                $sourceConflict = TimetableSlot::whereHas('teachingAssignment', function($q) use ($sourceTeacherId) {
                    $q->where('teacher_id', $sourceTeacherId);
                })->where('day_of_week', $validated['target_day_id'])
                ->where('period_id', $validated['target_period_id'])
                ->where('id', '!=', $sourceSlot->id)
                ->where('id', '!=', $targetSlot->id)
                ->exists();
                
                if ($sourceConflict) {
                    return ['type' => 'error', 'message' => 'មិនអាចប្ដូរបានទេ! ព្រោះគ្រូ ('. $sourceSlot->teachingAssignment->teacher->khmer_name .') ជាប់បង្រៀនថ្នាក់ផ្សេងនៅទីតាំងថ្មីនោះ។'];
                }

                $targetConflict = TimetableSlot::whereHas('teachingAssignment', function($q) use ($targetTeacherId) {
                    $q->where('teacher_id', $targetTeacherId);
                })->where('day_of_week', $sourceSlot->day_of_week)
                ->where('period_id', $sourceSlot->period_id)
                ->where('id', '!=', $targetSlot->id)
                ->where('id', '!=', $sourceSlot->id)
                ->exists();

                if ($targetConflict) {
                    return ['type' => 'error', 'message' => 'មិនអាចប្ដូរបានទេ! ព្រោះគ្រូ ('. $targetSlot->teachingAssignment->teacher->khmer_name .') ជាប់បង្រៀនថ្នាក់ផ្សេងនៅទីតាំងចាស់នោះ។'];
                }

                $targetShiftId = $targetSlot->period->shift_id;
                $sourceShiftId = $sourceSlot->period->shift_id;

                if ($targetSlot->day_of_week != $sourceSlot->day_of_week || $targetShiftId != $sourceShiftId) {
                    $sourceHoursTargetShift = $this->getHoursInShift($sourceSlot->teaching_assignment_id, $targetSlot->day_of_week, $targetSlot->period_id);
                    if ($sourceHoursTargetShift >= 2) {
                        return ['type' => 'error', 'message' => 'មិនអាចប្ដូរបានទេ! មុខវិជ្ជា ('. $sourceSlot->teachingAssignment->subject->khmer_name .') មាន ២ម៉ោងរួចហើយនៅពេលនោះ (ព្រឹក/រសៀល)។'];
                    }

                    $targetHoursSourceShift = $this->getHoursInShift($targetSlot->teaching_assignment_id, $sourceSlot->day_of_week, $sourceSlot->period_id);
                    if ($targetHoursSourceShift >= 2) {
                        return ['type' => 'error', 'message' => 'មិនអាចប្ដូរបានទេ! មុខវិជ្ជា ('. $targetSlot->teachingAssignment->subject->khmer_name .') មាន ២ម៉ោងរួចហើយនៅពេលនោះ (ព្រឹក/រសៀល)។'];
                    }
                }

                $tempDay = $sourceSlot->day_of_week;
                $tempPeriod = $sourceSlot->period_id;
                
                $sourceSlot->update([
                    'day_of_week' => $targetSlot->day_of_week,
                    'period_id' => $targetSlot->period_id,
                ]);
                
                $targetSlot->update([
                    'day_of_week' => $tempDay,
                    'period_id' => $tempPeriod,
                ]);

                return ['type' => 'success', 'message' => 'បានប្ដូរទីតាំងម៉ោងបង្រៀនដោយជោគជ័យ។'];

            } else {
                $sourceConflict = TimetableSlot::whereHas('teachingAssignment', function($q) use ($sourceTeacherId) {
                    $q->where('teacher_id', $sourceTeacherId);
                })->where('day_of_week', $validated['target_day_id'])
                ->where('period_id', $validated['target_period_id'])
                ->where('id', '!=', $sourceSlot->id)
                ->exists();
                
                if ($sourceConflict) {
                    return ['type' => 'error', 'message' => 'មិនអាចរំកិលបានទេ! ព្រោះគ្រូកំពុងជាប់បង្រៀនថ្នាក់ផ្សេងនៅទីតាំងថ្មីនោះ។'];
                }

                $targetShiftId = Period::find($validated['target_period_id'])->shift_id;
                if ($validated['target_day_id'] != $sourceSlot->day_of_week || $targetShiftId != $sourceSlot->period->shift_id) {
                    $sourceHoursTargetShift = $this->getHoursInShift($sourceSlot->teaching_assignment_id, $validated['target_day_id'], $validated['target_period_id']);
                    if ($sourceHoursTargetShift >= 2) {
                        return ['type' => 'error', 'message' => 'មិនអាចរំកិលបានទេ! មុខវិជ្ជា ('. $sourceSlot->teachingAssignment->subject->khmer_name .') មាន ២ម៉ោងរួចហើយនៅពេលនោះ (ព្រឹក/រសៀល)។'];
                    }
                }

                $sourceSlot->update([
                    'day_of_week' => $validated['target_day_id'],
                    'period_id' => $validated['target_period_id'],
                ]);

                return ['type' => 'success', 'message' => 'បានរំកិលទីតាំងម៉ោងបង្រៀនដោយជោគជ័យ។'];
            }
        });
    }

    public function truncateSlots()
    {
        if (auth()->check() && auth()->user()->school_id) {
            TimetableSlot::where('school_id', auth()->user()->school_id)->delete();
        } else {
            TimetableSlot::whereNull('school_id')->delete();
        }
    }
}
