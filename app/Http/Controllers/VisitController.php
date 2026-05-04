<?php

namespace App\Http\Controllers;

use App\Http\Requests\Visit\StoreVisitRequest;
use App\Http\Requests\Visit\UpdateVisitRequest;
use App\Repositories\VisitRepository;
use App\Models\Visit;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        if ($request->has('patient_id')) {
            $visits = $this->repository->allByPatientId($request->patient_id);
        } else {
            $visits = $this->repository->allWithRelations();
        }
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
        $data = $request->validated();

        if (!isset($data['medical_record_id']) && isset($data['patient_id'])) {
            $patient = User::findOrFail($data['patient_id']);
            $medicalRecord = $patient->medicalRecord;
            
            if (!$medicalRecord) {
                $medicalRecord = $patient->medicalRecord()->create([
                    'blood_type' => null,
                ]);
            }
            
            $data['medical_record_id'] = $medicalRecord->id;
        }

        $visit = $this->repository->create($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $visit->addMedia($file)->toMediaCollection('medical_files');
            }
        }

        return $this->createdResponse($visit->load('media'));
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
        $visit = $this->repository->update($id, $request->validated());
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

    /**
     * Upload files to an existing visit
     */
    public function uploadFiles(Request $request, string $id)
    {
        $visit = $this->repository->findOrFail($id);
        
        $request->validate([
            'files.*' => 'required|file|max:10240',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $visit->addMedia($file)->toMediaCollection('medical_files');
            }
        }

        return $this->successResponse($visit->load('media'), 'Files uploaded successfully');
    }
}
