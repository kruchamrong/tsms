<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class TeacherLeave extends Model
{
    use BelongsToSchool;
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = ['teacher_id', 'date_from', 'date_to', 'reason', 'status'];

    public function teacher() { return $this->belongsTo(Teacher::class)->withTrashed(); }
}
