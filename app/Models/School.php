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
        'province',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function onTrial()
    {
        if (is_null($this->trial_ends_at)) {
            return true; // No trial set means unlimited access (e.g., admin or old accounts)
        }

        return now()->lessThanOrEqualTo($this->trial_ends_at);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
