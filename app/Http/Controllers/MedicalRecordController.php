<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Traits\ApiResponse;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    /**
     * Get all medical records with patient and doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical records
     */
    public function index()
    {
        $records = MedicalRecord::with(['patient', 'doctor.user'])->get();
        return $this->successResponse($records);
    }

    /**
     * Create a new medical record
     *
     * @param StoreMedicalRecordRequest $request - Validated medical record data
     * @return \Illuminate\Http\JsonResponse - Created medical record
     */
    public function store(StoreMedicalRecordRequest $request)
    {
        $record = MedicalRecord::create($request->only([
            'patient_id',
            'doctor_id',
            'allergies',
            'chronic_diseases',
            'medications',
            'family_history',
            'notes',
        ]));
        return $this->createdResponse($record);
    }

    /**
     * Get specific medical record
     *
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Medical record details
     */
    public function show(string $id)
    {
        $record = MedicalRecord::with(['patient', 'doctor.user'])->findOrFail($id);
        return $this->successResponse($record);
    }

    /**
     * Update medical record
     *
     * @param UpdateMedicalRecordRequest $request - Validated medical record data
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Updated medical record
     */
    public function update(UpdateMedicalRecordRequest $request, string $id)
    {
        $record = MedicalRecord::findOrFail($id);
        $record->update($request->only([
            'patient_id',
            'doctor_id',
            'allergies',
            'chronic_diseases',
            'medications',
            'family_history',
            'notes',
        ]));
        return $this->successResponse($record);
    }

    /**
     * Delete a medical record
     *
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $record = MedicalRecord::findOrFail($id);
        $record->delete();
        return $this->successResponse(null, 'Medical record deleted successfully');
    }
}
