<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'visit_id' => $this->visit_id,
            'diagnosis' => $this->diagnosis,
            'notes' => $this->notes,
            'prescription_date' => $this->prescription_date,
            'status' => $this->status,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'name' => $this->patient->name,
                    'phone' => $this->patient->phone,
                ];
            }),
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'name' => $this->doctor->user?->name,
                    'specialization' => $this->doctor->specialization,
                ];
            }),
            'medications' => PrescriptionMedicationResource::collection($this->whenLoaded('medications')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
