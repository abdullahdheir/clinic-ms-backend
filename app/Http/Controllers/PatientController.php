<?php

namespace App\Http\Controllers;

use App\Repositories\PatientRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ApiResponse;

    /**
     * PatientController constructor.
     * 
     * @param PatientRepository $repository The patient repository instance.
     */
    public function __construct(
        private PatientRepository $repository
    ) {}

    /**
     * Display a listing of the patients.
     * 
     * @return JsonResponse List of patients.
     */
    public function index(): JsonResponse
    {
        $patients = $this->repository->allPatients();
        return $this->successResponse($patients);
    }

    /**
     * Display the specified patient.
     * 
     * @param int|string $id The patient ID.
     * @return JsonResponse The patient with medical records and appointments.
     */
    public function show(int|string $id): JsonResponse
    {
        $patient = $this->repository->findPatientOrFail($id);
        return $this->successResponse($patient);
    }

    /**
     * Update the specified patient in storage.
     * 
     * @param Request $request The update request.
     * @param int|string $id The patient ID.
     * @return JsonResponse The updated patient.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $patient = $this->repository->update($id, $request->all());
        return $this->successResponse($patient);
    }

    /**
     * Remove the specified patient from storage.
     * 
     * @param int|string $id The patient ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete($id);
        return $this->noContentResponse();
    }
}
