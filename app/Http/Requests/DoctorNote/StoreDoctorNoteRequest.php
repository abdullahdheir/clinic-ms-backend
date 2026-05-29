<?php

namespace App\Http\Requests\DoctorNote;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorNoteRequest extends FormRequest
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
            'content' => 'required|string',
            'note_type' => 'required|in:general,follow_up,urgent,routine',
            'is_pinned' => 'boolean',
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
            'content' => 'المحتوى',
            'note_type' => 'نوع الملاحظة',
            'is_pinned' => 'تثبيت',
        ];
    }
}
