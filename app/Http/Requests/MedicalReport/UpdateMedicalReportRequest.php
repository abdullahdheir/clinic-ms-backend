<?php

namespace App\Http\Requests\MedicalReport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalReportRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'results' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed,reviewed',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }
}
