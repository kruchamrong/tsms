<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class SchoolClass extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }
}
