<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class SubstituteAssignment extends Model
{
    use BelongsToSchool;
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = ['timetable_slot_id', 'substitute_teacher_id', 'date', 'status'];

    public function slot() { return $this->belongsTo(TimetableSlot::class, 'timetable_slot_id'); }
    public function substituteTeacher() { return $this->belongsTo(Teacher::class, 'substitute_teacher_id')->withTrashed(); }
}
