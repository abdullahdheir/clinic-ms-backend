<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Repositories\VisitRepository;
use App\Traits\ApiResponse;

class VisitController extends Controller
{
    use ApiResponse;

    public function __construct(
        private VisitRepository $repository
    ) {}

    /**
     * Get all visits with patient, doctor, and appointment details
     *
     * @return \Illuminate\Http\JsonResponse - List of visits
     */
    public function index()
    {
        $visits = $this->repository->allWithRelations();
        return $this->successResponse($visits);
    }

    /**
     * Create a new visit
     *
     * @param StoreVisitRequest $request - Validated visit data
     * @return \Illuminate\Http\JsonResponse - Created visit
     */
    public function store(StoreVisitRequest $request)
    {
        $visit = $this->repository->create($request->only([
            'patient_id',
            'doctor_id',
            'appointment_id',
            'visit_date',
            'chief_complaint',
            'diagnosis',
            'prescription',
            'notes',
            'visit_type',
        ]));
        return $this->createdResponse($visit);
    }

    /**
     * Get specific visit
     *
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Visit details
     */
    public function show(string $id)
    {
        $visit = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($visit);
    }

    /**
     * Update visit
     *
     * @param UpdateVisitRequest $request - Validated visit data
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Updated visit
     */
    public function update(UpdateVisitRequest $request, string $id)
    {
        $visit = $this->repository->update($id, $request->only([
            'patient_id',
            'doctor_id',
            'appointment_id',
            'visit_date',
            'chief_complaint',
            'diagnosis',
            'prescription',
            'notes',
            'visit_type',
        ]));
        return $this->successResponse($visit);
    }

    /**
     * Delete a visit
     *
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $this->repository->delete($id);
        return $this->successResponse(null, 'Visit deleted successfully');
    }
}
