<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function subjectGroup() {
        return $this->belongsTo(SubjectGroup::class);
    }
}
