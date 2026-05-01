<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Repositories\AppointmentRepository;
use App\Traits\ApiResponse;

class AppointmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AppointmentRepository $repository
    ) {}

    /**
     * Get all appointments with related data
     *
     * @return \Illuminate\Http\JsonResponse - List of appointments
     */
    public function index()
    {
        $appointments = $this->repository->allWithRelations();
        return $this->successResponse($appointments);
    }

    /**
     * Create a new appointment
     *
     * @param StoreAppointmentRequest $request - Validated appointment data
     * @return \Illuminate\Http\JsonResponse - Created appointment
     */
    public function store(StoreAppointmentRequest $request)
    {
        $appointment = $this->repository->create($request->only([
            'patient_id',
            'doctor_id',
            'department_id',
            'appointment_date',
            'reason',
            'notes',
        ]));
        return $this->createdResponse($appointment);
    }

    /**
     * Get specific appointment
     *
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Appointment details
     */
    public function show(string $id)
    {
        $appointment = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($appointment);
    }

    /**
     * Update appointment status or details
     *
     * @param UpdateAppointmentRequest $request - Validated appointment data
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Updated appointment
     */
    public function update(UpdateAppointmentRequest $request, string $id)
    {
        $appointment = $this->repository->update($id, $request->only([
            'patient_id',
            'doctor_id',
            'department_id',
            'appointment_date',
            'status',
            'reason',
            'notes',
            'checked_in_at',
            'checked_in_by',
        ]));
        return $this->successResponse($appointment);
    }

    /**
     * Delete an appointment
     *
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $this->repository->delete($id);
        return $this->successResponse(null, 'Appointment deleted successfully');
    }
}
