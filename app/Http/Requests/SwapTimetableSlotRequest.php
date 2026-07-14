<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwapTimetableSlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        return [
            'source_slot_id' => ['required', Rule::exists('timetable_slots', 'id')->where($schoolIdCheck)],
            'target_class_id' => ['required', Rule::exists('school_classes', 'id')->where($schoolIdCheck)],
            'target_day_id' => 'required|integer',
            'target_period_id' => ['required', 'integer', Rule::exists('periods', 'id')->where($schoolIdCheck)],
        ];
    }
}
