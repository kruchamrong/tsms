<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'teacher_code' => [
                'required', 'string', 'max:255',
                Rule::unique('teachers')->ignore($this->teacher)->where(function ($query) {
                    if (auth()->check() && auth()->user()->school_id) {
                        return $query->where('school_id', auth()->user()->school_id);
                    }
                })
            ],
            'khmer_name' => ['required', 'string', 'max:255'],
            'english_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('teachers')->ignore($this->teacher)->where(function ($query) {
                    if (auth()->check() && auth()->user()->school_id) {
                        return $query->where('school_id', auth()->user()->school_id);
                    }
                })
            ],
            'qualification' => ['nullable', 'string', 'max:255'],
            'remove_photo' => ['nullable', 'boolean'],
            'employment_type' => ['required', 'in:Full-Time,Part-Time,Visiting'],
            'status' => ['required', 'in:Active,Inactive,On-Leave'],
        ];

        if ($this->hasFile('photo')) {
            $rules['photo'] = ['image', 'max:2048'];
        } else {
            $rules['photo'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
