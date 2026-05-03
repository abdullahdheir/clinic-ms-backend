<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
            'clinic_id' => 'sometimes|required|exists:clinics,id',
            'name' => 'sometimes|required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'max_capacity' => 'integer|min:1',
            'description' => 'nullable|string',
        ];
    }
}
