<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;

class Subject extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'subject_code', 
        'khmer_name', 
        'english_name', 
        'short_name',
        'color',
        'subject_group_id',
        'school_id'
    ];

    protected static function booted()
    {
        static::updating(function ($subject) {
            if ($subject->school_id === null && auth()->check() && auth()->user()->role !== 'super_admin') {
                abort(403, 'មិនអាចកែប្រែមុខវិជ្ជាគំរូបានទេ!');
            }
        });

        static::deleting(function ($subject) {
            if ($subject->school_id === null && auth()->check() && auth()->user()->role !== 'super_admin') {
                abort(403, 'មិនអាចលុបមុខវិជ្ជាគំរូបានទេ!');
            }
        });
    }

    public function subjectGroup()
    {
        return $this->belongsTo(SubjectGroup::class);
    }

    public function curricula()
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_subject')
                    ->withPivot('weekly_hours')
                    ->withTimestamps();
    }
}
