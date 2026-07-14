<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReassignTeachingAssignmentRequest extends FormRequest
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
            'assignment_id' => ['required', Rule::exists('teaching_assignments', 'id')->where($schoolIdCheck)],
            'new_teacher_id' => ['required', Rule::exists('teachers', 'id')->where($schoolIdCheck)],
            'new_subject_id' => ['required', Rule::exists('subjects', 'id')->where($schoolIdCheck)],
        ];
    }
}
