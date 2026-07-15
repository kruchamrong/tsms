<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherDocument extends Model
{
    use BelongsToSchool;
    use HasFactory, HasUuids;

    protected $fillable = [
        'teacher_id',
        'title',
        'file_path',
        'file_type',
        'original_name',
    ];
}
