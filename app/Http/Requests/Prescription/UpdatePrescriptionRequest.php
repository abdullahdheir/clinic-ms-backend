<?php

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
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
            'diagnosis' => 'sometimes|string',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:active,completed,cancelled',
            'medications' => 'sometimes|array',
            'medications.*.id' => 'sometimes|integer|exists:prescription_medications,id',
            'medications.*.medication_name' => 'required_with:medications|string',
            'medications.*.dosage' => 'required_with:medications|string',
            'medications.*.frequency' => 'required_with:medications|string',
            'medications.*.duration' => 'required_with:medications|string',
            'medications.*.instructions' => 'nullable|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'diagnosis' => 'التشخيص',
            'notes' => 'الملاحظات',
            'status' => 'الحالة',
            'medications' => 'الأدوية',
        ];
    }
}
