<?php
namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\TeacherAvailability;
use App\Models\Room;
use App\Models\Period;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Notifications\SystemAlert;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TimetableEngine
{
    private $assignments;
    private $availabilities;
    private $rooms;
    private $periods;
    private $days = [1, 2, 3, 4, 5, 6]; // 1=Mon, 6=Sat

    public function generate()
    {
        // Clear all unlocked existing slots (for now just truncate)
        if (auth()->check() && auth()->user()->school_id) {
            TimetableSlot::where('school_id', auth()->user()->school_id)->delete();
        } else {
            TimetableSlot::whereNull('school_id')->delete();
        }

        // 1. Load Data
        $this->assignments = TeachingAssignment::with(['teacher', 'subject', 'schoolClass'])->get();
        
        $availData = TeacherAvailability::all();
        $this->availabilities = [];
        foreach ($availData as $avail) {
            $this->availabilities[$avail->teacher_id][$avail->day_of_week][$avail->shift_id] = $avail->is_available;
        }

        $this->rooms = Room::all();
        if ($this->rooms->isEmpty()) {
            return ['status' => 'error', 'message' => 'No rooms available. Please add rooms first.'];
        }

        $periodData = Period::all();
        if ($periodData->isEmpty()) {
            return ['status' => 'error', 'message' => 'No periods configured. Please setup shift periods first.'];
        }
        
        $this->periods = [];
        foreach ($periodData as $p) {
            $this->periods[$p->shift_id][] = $p;
        }

        // Ensure periods are ordered by start_time for consecutive checks
        foreach ($this->periods as $shiftId => $periodsArray) {
            usort($periodsArray, function($a, $b) {
                return $a->start_time <=> $b->start_time;
            });
            $this->periods[$shiftId] = $periodsArray;
        }

        // 2. Break down into 1-hour blocks
        $blocksToSchedule = [];
        foreach ($this->assignments as $assignment) {
            for ($i = 0; $i < $assignment->weekly_hours; $i++) {
                $blocksToSchedule[] = $assignment;
            }
        }

        if (empty($blocksToSchedule)) {
            return ['status' => 'warning', 'message' => 'No teaching assignments to schedule.'];
        }

        // Sort blocks by MRV heuristic: schedule teachers with fewest availability first
        // We sort by weekly_hours descending so that classes with more hours are placed first
        usort($blocksToSchedule, function($a, $b) {
            return $b->weekly_hours <=> $a->weekly_hours;
        });

        // 3. Greedy Search (Fast, no infinite hangs)
        $schedule = []; 
        $success = $this->greedySchedule($blocksToSchedule, $schedule);

        // 4. Save results
        if ($success) {
            $this->saveSchedule($schedule);
            
            // Notify admins
            $schoolId = auth()->check() ? auth()->user()->school_id : null;
            $admins = User::where(function($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
                $q->orWhereNull('school_id');
            })->get();
            foreach ($admins as $admin) {
                $admin->notify(new SystemAlert('កាលវិភាគត្រូវបានបង្កើតដោយជោគជ័យ', 'កាលវិភាគថ្មីត្រូវបានបង្កើតដោយជោគជ័យ និងគ្មានការជាន់ម៉ោងនោះទេ។'));
            }

            return ['status' => 'success', 'message' => 'Timetable generated successfully. 100% of hours assigned.'];
        } else {
            // Save whatever we managed to schedule before failing
            $this->saveSchedule($schedule);
            
            $schoolId = auth()->check() ? auth()->user()->school_id : null;
            $admins = User::where(function($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
                $q->orWhereNull('school_id');
            })->get();
            foreach ($admins as $admin) {
                $admin->notify(new SystemAlert('កាលវិភាគត្រូវបានបង្កើត (មានបញ្ហាជាន់ម៉ោង)', 'កាលវិភាគថ្មីត្រូវបានបង្កើត ប៉ុន្តែថ្នាក់មួយចំនួនមិនអាចរៀបចំម៉ោងបានឡើយ។'));
            }

            return ['status' => 'warning', 'message' => 'Generation complete but with conflicts. Some classes could not be placed due to tight constraints.'];
        }
    }

    private function greedySchedule(&$blocks, &$schedule)
    {
        $unplaced = 0;
        foreach ($blocks as $assignment) {
            $shiftId = $assignment->shift_id;
            $teacherId = $assignment->teacher_id;
            $classId = $assignment->school_class_id;
            $homeRoomId = $assignment->schoolClass->room_id ?? null;
            
            $availablePeriods = $this->periods[$shiftId] ?? [];
            
            $days = $this->days;
            shuffle($days);
            
            $rooms = $this->rooms->toArray();
            if ($homeRoomId) {
                usort($rooms, function($a, $b) use ($homeRoomId) {
                    if ($a['id'] == $homeRoomId) return -1;
                    if ($b['id'] == $homeRoomId) return 1;
                    return 0;
                });
            } else {
                shuffle($rooms);
            }

            $placed = false;

            foreach ($days as $day) {
                $isAvail = $this->availabilities[$teacherId][$day][$shiftId] ?? true; 
                if (!$isAvail) continue;

                foreach ($availablePeriods as $index => $period) {
                    foreach ($rooms as $room) {
                        $roomId = $room['id'];
                        if ($this->isValid($schedule, $day, $period->id, $roomId, $teacherId, $classId, $availablePeriods, $index)) {
                            $schedule[$day][$period->id][$roomId] = $assignment;
                            $placed = true;
                            break 3; // Break out of room, period, day loops
                        }
                    }
                }
            }
            if (!$placed) {
                $unplaced++;
            }
        }
        
        return $unplaced === 0;
    }

    private function isValid(&$schedule, $day, $periodId, $roomId, $teacherId, $classId, $availablePeriods, $periodIndex)
    {
        // 1. Basic conflicts
        if (isset($schedule[$day][$periodId])) {
            // Room conflict
            if (isset($schedule[$day][$periodId][$roomId])) {
                return false;
            }

            // Teacher or Class conflict
            foreach ($schedule[$day][$periodId] as $assignedRoom => $assignedAssignment) {
                if ($assignedAssignment->teacher_id === $teacherId) {
                    return false;
                }
                if ($assignedAssignment->school_class_id === $classId) {
                    return false;
                }
            }
        }

        // 2. Max consecutive periods for teacher constraint
        $maxConsecutive = 3;
        $consecutiveCount = 1; // including this one

        // Look backwards
        for ($i = $periodIndex - 1; $i >= 0; $i--) {
            $pId = $availablePeriods[$i]->id;
            if ($this->isTeacherTeaching($schedule, $day, $pId, $teacherId)) {
                $consecutiveCount++;
            } else {
                break;
            }
        }

        // Look forwards
        for ($i = $periodIndex + 1; $i < count($availablePeriods); $i++) {
            $pId = $availablePeriods[$i]->id;
            if ($this->isTeacherTeaching($schedule, $day, $pId, $teacherId)) {
                $consecutiveCount++;
            } else {
                break;
            }
        }

        if ($consecutiveCount > $maxConsecutive) {
            return false;
        }

        return true;
    }

    private function isTeacherTeaching(&$schedule, $day, $periodId, $teacherId) {
        if (!isset($schedule[$day][$periodId])) return false;
        foreach ($schedule[$day][$periodId] as $assignedRoom => $assignedAssignment) {
            if ($assignedAssignment->teacher_id === $teacherId) {
                return true;
            }
        }
        return false;
    }

    private function saveSchedule(&$schedule)
    {
        $inserts = [];
        $now = now();
        $schoolId = auth()->check() ? auth()->user()->school_id : null;
        foreach ($schedule as $day => $periods) {
            foreach ($periods as $periodId => $rooms) {
                foreach ($rooms as $roomId => $assignment) {
                    $inserts[] = [
                        'id' => (string) Str::uuid(),
                        'teaching_assignment_id' => $assignment->id,
                        'period_id' => $periodId,
                        'day_of_week' => $day,
                        'room_id' => $roomId,
                        'status' => 'Scheduled',
                        'school_id' => $schoolId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($inserts)) {
            $chunks = array_chunk($inserts, 500);
            foreach ($chunks as $chunk) {
                TimetableSlot::insert($chunk);
            }
        }
    }
}
