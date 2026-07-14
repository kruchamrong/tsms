<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class Period extends Model
{
    use BelongsToSchool;
    protected $fillable = ['start_time', 'end_time', 'shift_id', 'school_id'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
