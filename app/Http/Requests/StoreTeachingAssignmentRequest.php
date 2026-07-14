<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeachingAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
            'teacher_id' => ['required', Rule::exists('teachers', 'id')->where($schoolIdCheck)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where($schoolIdCheck)],
            'school_class_ids' => 'required|array|min:1',
            'school_class_ids.*' => [Rule::exists('school_classes', 'id')->where($schoolIdCheck)],
        ];
    }
}
