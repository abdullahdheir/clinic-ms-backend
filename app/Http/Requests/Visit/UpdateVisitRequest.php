<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_record_id' => 'sometimes|required|exists:medical_records,id',
            'appointment_id' => 'sometimes|required|exists:appointments,id',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'clinic_id' => 'sometimes|required|exists:clinics,id',
            'diagnosis' => 'sometimes|required|string',
            'prescription' => 'nullable|array',
            'notes' => 'nullable|string',
            'visited_at' => 'nullable|date',
        ];
    }
}
