<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Repositories\MedicalRecordRepository;
use App\Traits\ApiResponse;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MedicalRecordRepository $repository
    ) {}

    /**
     * Get all medical records with patient and doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical records
     */
    public function index()
    {
        $records = $this->repository->allWithRelations();
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
        $record = $this->repository->create($request->only([
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
        $record = $this->repository->findWithRelationsOrFail($id);
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
        $record = $this->repository->update($id, $request->only([
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
        $this->repository->delete($id);
        return $this->successResponse(null, 'Medical record deleted successfully');
    }
}
