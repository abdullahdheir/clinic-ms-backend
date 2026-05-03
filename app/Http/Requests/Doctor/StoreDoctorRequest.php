<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'bio' => 'nullable|string',
            'specialization' => 'required|string|max:255',
            'session_duration_minutes' => 'nullable|integer|min:5',
            'consultation_fee' => 'required|numeric|min:0',
        ];
    }
}
