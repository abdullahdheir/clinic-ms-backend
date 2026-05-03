<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'bio' => 'nullable|string',
            'specialization' => 'sometimes|required|string|max:255',
            'session_duration_minutes' => 'nullable|integer|min:5',
            'consultation_fee' => 'sometimes|required|numeric|min:0',
        ];
    }
}
