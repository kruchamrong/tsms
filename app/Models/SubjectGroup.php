<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class SubjectGroup extends Model
{
    use BelongsToSchool;
    protected $fillable = ['name', 'school_id'];
}
