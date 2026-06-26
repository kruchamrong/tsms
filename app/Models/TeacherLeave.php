<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherLeave extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = ['teacher_id', 'date_from', 'date_to', 'reason', 'status'];

    public function teacher() { return $this->belongsTo(Teacher::class); }
}
