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
        return [
            'teacher_code' => ['required', 'string', 'max:255', Rule::unique('teachers')->ignore($this->teacher)],
            'khmer_name' => ['required', 'string', 'max:255'],
            'english_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('teachers')->ignore($this->teacher)],
            'qualification' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:Full-Time,Part-Time,Visiting'],
            'status' => ['required', 'in:Active,Inactive,On-Leave'],
        ];
    }
}
