<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_record_id' => 'required_without:patient_id|exists:medical_records,id',
            'patient_id' => 'required_without:medical_record_id|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'diagnosis' => 'required|string',
            'prescription' => 'nullable|array',
            'notes' => 'nullable|string',
            'visited_at' => 'nullable|date',
            'files.*' => 'nullable|file|max:10240',
        ];
    }
}
