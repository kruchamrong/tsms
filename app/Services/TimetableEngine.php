<?php
namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\TeacherAvailability;
use App\Models\Room;
use App\Models\Period;
use App\Models\TimetableSlot;
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
        TimetableSlot::truncate();

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
        // (Simplified for now: random order)
        shuffle($blocksToSchedule);

        // 3. Recursive Backtracking Search
        $schedule = []; 
        $success = $this->backtrack($blocksToSchedule, $schedule, 0);

        // 4. Save results
        if ($success) {
            $this->saveSchedule($schedule);
            return ['status' => 'success', 'message' => 'Timetable generated successfully. 100% of hours assigned.'];
        } else {
            // Save whatever we managed to schedule before failing
            $this->saveSchedule($schedule);
            return ['status' => 'warning', 'message' => 'Generation complete but with conflicts. Some classes could not be placed due to tight constraints.'];
        }
    }

    private function backtrack(&$blocks, &$schedule, $index)
    {
        // Base case: all blocks placed
        if ($index >= count($blocks)) {
            return true;
        }

        $assignment = $blocks[$index];
        $shiftId = $assignment->shift_id;
        $teacherId = $assignment->teacher_id;
        $classId = $assignment->school_class_id;
        
        $availablePeriods = $this->periods[$shiftId] ?? [];
        
        $days = $this->days;
        shuffle($days);
        
        $rooms = $this->rooms->shuffle();

        foreach ($days as $day) {
            // Check Teacher Availability rules
            $isAvail = $this->availabilities[$teacherId][$day][$shiftId] ?? true; 
            if (!$isAvail) continue;

            foreach ($availablePeriods as $period) {
                // To spread out subjects, optionally check if this class already has this subject today
                // (Skipped for simplicity in initial version)

                foreach ($rooms as $room) {
                    if ($this->isValid($schedule, $day, $period->id, $room->id, $teacherId, $classId)) {
                        // Forward check / Domain reduction
                        $schedule[$day][$period->id][$room->id] = $assignment;
                        
                        // Recurse
                        if ($this->backtrack($blocks, $schedule, $index + 1)) {
                            return true;
                        }
                        
                        // Undo (Backtrack)
                        unset($schedule[$day][$period->id][$room->id]);
                    }
                }
            }
        }
        
        // If we get here, no valid slot was found for this block
        return false;
    }

    private function isValid(&$schedule, $day, $periodId, $roomId, $teacherId, $classId)
    {
        if (!isset($schedule[$day][$periodId])) {
            return true;
        }

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

        return true;
    }

    private function saveSchedule(&$schedule)
    {
        $inserts = [];
        $now = now();
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
