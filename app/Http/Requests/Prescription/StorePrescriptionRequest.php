<?php

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
            'patient_id' => 'required|integer|exists:users,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'visit_id' => 'nullable|integer|exists:visits,id',
            'diagnosis' => 'required|string',
            'notes' => 'nullable|string',
            'prescription_date' => 'required|date',
            'medications' => 'required|array|min:1',
            'medications.*.medication_name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.duration' => 'required|string',
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
            'patient_id' => 'المريض',
            'doctor_id' => 'الطبيب',
            'visit_id' => 'الزيارة',
            'diagnosis' => 'التشخيص',
            'notes' => 'الملاحظات',
            'prescription_date' => 'تاريخ الوصفة',
            'medications' => 'الأدوية',
            'medications.*.medication_name' => 'اسم الدواء',
            'medications.*.dosage' => 'الجرعة',
            'medications.*.frequency' => 'التكرار',
            'medications.*.duration' => 'المدة',
        ];
    }
}
