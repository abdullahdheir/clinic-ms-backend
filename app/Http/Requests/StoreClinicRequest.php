<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'logo_url' => 'nullable|url',
            'manager_id' => 'nullable|exists:users,id',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}
