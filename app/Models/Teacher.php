<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use BelongsToSchool;
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function homeroom_classes()
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }
}
