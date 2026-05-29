<?php

namespace App\Http\Requests\Xray;

use Illuminate\Foundation\Http\FormRequest;

class StoreXrayRequest extends FormRequest
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
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'visit_id' => 'nullable|integer|exists:visits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_type' => 'required|string',
            'xray_date' => 'required|date',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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
            'title' => 'العنوان',
            'image_type' => 'نوع الأشعة',
            'xray_date' => 'تاريخ الأشعة',
            'image' => 'الصورة',
        ];
    }
}
