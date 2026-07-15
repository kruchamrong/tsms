<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class Room extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
