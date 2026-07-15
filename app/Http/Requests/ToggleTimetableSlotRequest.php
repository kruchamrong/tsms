<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleTimetableSlotRequest extends FormRequest
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
            'school_class_id' => ['required', Rule::exists('school_classes', 'id')->where($schoolIdCheck)],
            'day_of_week' => 'required|integer|min:1|max:7',
            'period_id' => ['required', Rule::exists('periods', 'id')->where($schoolIdCheck)],
            'teacher_id' => ['required', Rule::exists('teachers', 'id')->where($schoolIdCheck)],
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')->where($schoolIdCheck)],
        ];
    }
}
