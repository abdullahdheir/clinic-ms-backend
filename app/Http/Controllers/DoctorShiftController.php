<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorShiftRequest;
use App\Http\Requests\UpdateDoctorShiftRequest;
use App\Models\DoctorShift;
use App\Traits\ApiResponse;

class DoctorShiftController extends Controller
{
    use ApiResponse;

    /**
     * Get all doctor shifts with doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of doctor shifts
     */
    public function index()
    {
        $shifts = DoctorShift::with('doctor.user')->get();
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
        $shift = DoctorShift::create($request->only([
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
        $shift = DoctorShift::with('doctor.user')->findOrFail($id);
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
        $shift = DoctorShift::findOrFail($id);
        $shift->update($request->only([
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
        $shift = DoctorShift::findOrFail($id);
        $shift->delete();
        return $this->successResponse(null, 'Doctor shift deleted successfully');
    }
}
