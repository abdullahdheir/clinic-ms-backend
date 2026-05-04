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
            'medical_record_id' => 'required|exists:medical_records,id',
            'appointment_id' => 'required|exists:appointments,id',
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
