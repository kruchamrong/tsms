<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class Shift extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
}
