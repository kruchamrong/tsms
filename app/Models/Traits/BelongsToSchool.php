<?php

namespace App\Models\Traits;

use App\Models\School;
use App\Models\Scopes\SchoolScope;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool()
    {
        static::addGlobalScope(new SchoolScope);

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->school_id) {
                $model->school_id = auth()->user()->school_id;
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && auth()->user()->school_id) {
                // Prevent changing school_id to another school
                $model->school_id = auth()->user()->school_id;
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
