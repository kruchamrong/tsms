<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class TeachingAssignment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use BelongsToSchool;
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'school_class_id',
        'shift_id',
        'weekly_hours',
        'academic_year_id',
        'semester_id'
    ];

    public function teacher() { return $this->belongsTo(Teacher::class)->withTrashed(); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'school_class_id'); }
    public function shift() { return $this->belongsTo(Shift::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function semester() { return $this->belongsTo(Semester::class); }
    public function timetableSlots() { return $this->hasMany(TimetableSlot::class); }
}
