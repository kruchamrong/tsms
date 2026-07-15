<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class Curriculum extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = ['name', 'description', 'sort_order', 'school_id'];

    protected static function booted()
    {
        static::updating(function ($curriculum) {
            if ($curriculum->school_id === null && auth()->check() && auth()->user()->role !== 'super_admin') {
                abort(403, 'មិនអាចកែប្រែកម្មវិធីសិក្សាគំរូបានទេ!');
            }
        });

        static::deleting(function ($curriculum) {
            if ($curriculum->school_id === null && auth()->check() && auth()->user()->role !== 'super_admin') {
                abort(403, 'មិនអាចលុបកម្មវិធីសិក្សាគំរូបានទេ!');
            }
        });
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'curriculum_subject')
                    ->withPivot('weekly_hours', 'sort_order')
                    ->orderByPivot('sort_order')
                    ->withTimestamps();
    }
}
