<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeacherAvailabilityRemark extends Model
{
    use BelongsToSchool;
    use HasFactory, HasUuids;

    protected $fillable = [
        'teacher_id',
        'day_of_week',
        'remarks',
    ];
}
