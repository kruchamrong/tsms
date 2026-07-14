<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSchool;
use OwenIt\Auditing\Contracts\Auditable;

class TimetableSlot extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use BelongsToSchool;
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'teaching_assignment_id',
        'period_id',
        'day_of_week',
        'room_id',
        'status',
    ];

    public function teachingAssignment() { return $this->belongsTo(TeachingAssignment::class); }
    public function period() { return $this->belongsTo(Period::class); }
    public function room() { return $this->belongsTo(Room::class); }
}
