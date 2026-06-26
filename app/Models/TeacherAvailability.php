<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAvailability extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'teacher_id',
        'day_of_week',
        'shift_id',
        'is_available'
    ];

    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function shift() { return $this->belongsTo(Shift::class); }
}
