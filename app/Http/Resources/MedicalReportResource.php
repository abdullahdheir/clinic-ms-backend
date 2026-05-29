<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'visit_id' => $this->visit_id,
            'title' => $this->title,
            'description' => $this->description,
            'report_type' => $this->report_type,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path ? url('storage/' . $this->file_path) : null,
            'file_type' => $this->file_type,
            'report_date' => $this->report_date,
            'results' => $this->results,
            'recommendations' => $this->recommendations,
            'status' => $this->status,
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
}
