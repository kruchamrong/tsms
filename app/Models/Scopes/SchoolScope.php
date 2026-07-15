<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->school_id) {
            // Models that are globally shared (school_id = null)
            $globalModels = [
                \App\Models\Grade::class,
                \App\Models\Shift::class,
                \App\Models\SubjectGroup::class,
                \App\Models\Period::class,
                \App\Models\AcademicYear::class,
                \App\Models\Semester::class,
            ];

            $builder->where(function ($query) use ($model, $globalModels) {
                $query->where($model->getTable() . '.school_id', auth()->user()->school_id);
                
                if (in_array(get_class($model), $globalModels)) {
                    $query->orWhereNull($model->getTable() . '.school_id');
                }
            });
        }
    }
}
