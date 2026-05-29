<?php

namespace App\Http\Requests\DoctorNote;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorNoteRequest extends FormRequest
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
            'content' => 'sometimes|string',
            'note_type' => 'sometimes|in:general,follow_up,urgent,routine',
            'is_pinned' => 'boolean',
        ];
    }
}
