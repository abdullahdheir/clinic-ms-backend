<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blood_type' => 'nullable|string|max:5',
            'chronic_diseases' => 'nullable|array',
            'allergies' => 'nullable|array',
            'emergency_contact' => 'nullable|string|max:255',
            'medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
