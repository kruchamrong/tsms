<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $guarded = [];

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function subjectGroup() {
        return $this->belongsTo(SubjectGroup::class);
    }
}
