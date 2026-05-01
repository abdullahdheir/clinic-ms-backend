<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorShiftRequest;
use App\Http\Requests\UpdateDoctorShiftRequest;
use App\Repositories\DoctorShiftRepository;
use App\Traits\ApiResponse;

class DoctorShiftController extends Controller
{
    use ApiResponse;

    public function __construct(
        private DoctorShiftRepository $repository
    ) {}

    /**
     * Get all doctor shifts with doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of doctor shifts
     */
    public function index()
    {
        $shifts = $this->repository->allWithRelations();
        return $this->successResponse($shifts);
    }

    /**
     * Create a new doctor shift
     *
     * @param StoreDoctorShiftRequest $request - Validated shift data
     * @return \Illuminate\Http\JsonResponse - Created shift
     */
    public function store(StoreDoctorShiftRequest $request)
    {
        $shift = $this->repository->create($request->only([
            'doctor_id',
            'day_of_week',
            'start_time',
            'end_time',
            'is_active',
        ]));
        return $this->createdResponse($shift);
    }

    /**
     * Get specific doctor shift
     *
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Shift details
     */
    public function show(string $id)
    {
        $shift = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($shift);
    }

    /**
     * Update doctor shift
     *
     * @param UpdateDoctorShiftRequest $request - Validated shift data
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Updated shift
     */
    public function update(UpdateDoctorShiftRequest $request, string $id)
    {
        $shift = $this->repository->update($id, $request->only([
            'doctor_id',
            'day_of_week',
            'start_time',
            'end_time',
            'is_active',
        ]));
        return $this->successResponse($shift);
    }

    /**
     * Delete a doctor shift
     *
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $this->repository->delete($id);
        return $this->successResponse(null, 'Doctor shift deleted successfully');
    }
}
