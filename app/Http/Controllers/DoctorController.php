<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Traits\ApiResponse;

class DoctorController extends Controller
{
    use ApiResponse;

    /**
     * Get all doctors with user and department details
     *
     * @return \Illuminate\Http\JsonResponse - List of doctors
     */
    public function index()
    {
        $doctors = Doctor::with(['user', 'department'])->get();
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
        $doctor = Doctor::create($request->only([
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
        $doctor = Doctor::with(['user', 'department.clinic'])->findOrFail($id);
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
        $doctor = Doctor::findOrFail($id);
        $doctor->update($request->only([
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
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();
        return $this->successResponse(null, 'Doctor deleted successfully');
    }
}
