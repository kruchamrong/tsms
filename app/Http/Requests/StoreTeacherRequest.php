<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow for now
    }

    public function rules(): array
    {
        return [
            'teacher_code' => ['required', 'string', 'max:255', 'unique:teachers,teacher_code'],
            'khmer_name' => ['required', 'string', 'max:255'],
            'english_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:teachers,email'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:Full-Time,Part-Time,Visiting'],
            'status' => ['required', 'in:Active,Inactive,On-Leave'],
        ];
    }
}
