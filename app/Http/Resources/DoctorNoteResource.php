<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'visit_id' => $this->visit_id,
            'content' => $this->content,
            'note_type' => $this->note_type,
            'note_type_label' => $this->getNoteTypeLabel($this->note_type),
            'is_pinned' => $this->is_pinned,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'name' => $this->patient->name,
                ];
            }),
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'name' => $this->doctor->user?->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getNoteTypeLabel($type)
    {
        $labels = [
            'general' => 'عام',
            'follow_up' => 'متابعة',
            'urgent' => 'عاجل',
            'routine' => 'روتيني',
        ];

        return $labels[$type] ?? $type;
    }
}
