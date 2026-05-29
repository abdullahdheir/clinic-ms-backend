<?php

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'event_type' => 'required|in:appointment,reminder,block,meeting',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
            'color' => 'nullable|string',
            'is_all_day' => 'boolean',
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
            'title' => 'العنوان',
            'start_time' => 'وقت البداية',
            'end_time' => 'وقت النهاية',
            'event_type' => 'نوع الحدث',
        ];
    }
}
