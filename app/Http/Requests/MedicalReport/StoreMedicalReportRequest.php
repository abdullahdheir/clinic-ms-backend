<?php

namespace App\Http\Requests\MedicalReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalReportRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'report_type' => 'required|string',
            'report_date' => 'required|date',
            'results' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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
            'title' => 'العنوان',
            'description' => 'الوصف',
            'report_type' => 'نوع التقرير',
            'report_date' => 'تاريخ التقرير',
            'file' => 'الملف',
        ];
    }
}
