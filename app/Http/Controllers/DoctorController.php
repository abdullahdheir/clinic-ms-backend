<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Repositories\DoctorRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    use ApiResponse;

    /**
     * DoctorController constructor.
     * 
     * @param DoctorRepository $repository The doctor repository instance.
     */
    public function __construct(
        private DoctorRepository $repository
    ) {}

    /**
     * Display a listing of the doctors.
     * 
     * @return JsonResponse List of doctors with user and department details.
     */
    public function index(): JsonResponse
    {
        $doctors = $this->repository->allWithRelations();
        return $this->successResponse($doctors);
    }

    /**
     * Create a new doctor profile
     *
     * @param StoreDoctorRequest $request - Validated doctor data
     * @return \Illuminate\Http\JsonResponse - Created doctor
     */
    public function store(StoreDoctorRequest $request)
    {
        $doctor = $this->repository->create($request->only([
            'user_id',
            'department_id',
            'bio',
            'specialization',
            'session_duration_minutes',
            'consultation_fee',
        ]));
        return $this->createdResponse($doctor);
    }

    /**
     * Get specific doctor with user and department
     *
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Doctor details
     */
    public function show(string $id)
    {
        $doctor = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($doctor);
    }

    /**
     * Update doctor details
     *
     * @param UpdateDoctorRequest $request - Validated doctor data
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Updated doctor
     */
    public function update(UpdateDoctorRequest $request, string $id)
    {
        $doctor = $this->repository->update($id, $request->only([
            'user_id',
            'department_id',
            'bio',
            'specialization',
            'session_duration_minutes',
            'consultation_fee',
        ]));
        return $this->successResponse($doctor);
    }

    /**
     * Delete a doctor
     *
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $this->repository->delete($id);
        return $this->successResponse(null, 'Doctor deleted successfully');
    }
}
