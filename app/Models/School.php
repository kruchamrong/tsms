<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'education_level',
        'logo',
        'principal_name',
        'phone',
        'address',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
