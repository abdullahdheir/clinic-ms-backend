<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecord\UpdateMedicalRecordRequest;
use App\Repositories\MedicalRecordRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    /**
     * MedicalRecordController constructor.
     * 
     * @param MedicalRecordRepository $repository The medical record repository instance.
     */
    public function __construct(
        private MedicalRecordRepository $repository
    ) {}

    /**
     * Display the specified medical record by patient ID.
     * 
     * @param int|string $patientId The patient ID.
     * @return JsonResponse The medical record with patient info and visits.
     */
    public function show(int|string $patientId): JsonResponse
    {
        $medicalRecord = $this->repository->findByPatientId($patientId);
        return $this->successResponse($medicalRecord);
    }

    /**
     * Update the specified medical record in storage.
     * 
     * @param UpdateMedicalRecordRequest $request The validated update request.
     * @param int|string $id The medical record ID.
     * @return JsonResponse The updated medical record.
     */
    public function update(UpdateMedicalRecordRequest $request, int|string $id): JsonResponse
    {
        $medicalRecord = $this->repository->update($id, $request->validated());
        return $this->successResponse($medicalRecord, 'Medical record updated successfully');
    }
}
