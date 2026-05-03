<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentStatusRequest;
use App\Repositories\AppointmentRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    use ApiResponse;

    /**
     * AppointmentController constructor.
     * 
     * @param AppointmentRepository $repository The appointment repository instance.
     */
    public function __construct(
        private AppointmentRepository $repository
    ) {}

    /**
     * Display a listing of the appointments.
     * 
     * @return JsonResponse List of appointments with relations.
     */
    public function index(): JsonResponse
    {
        $appointments = $this->repository->allWithRelations();
        return $this->successResponse($appointments);
    }

    /**
     * Display today's appointments.
     * 
     * @return JsonResponse List of today's appointments.
     */
    public function today(): JsonResponse
    {
        $appointments = $this->repository->getTodayAppointments();
        return $this->successResponse($appointments);
    }

    /**
     * Store a newly created appointment in storage.
     * 
     * @param StoreAppointmentRequest $request The validated request containing appointment data.
     * @return JsonResponse The created appointment.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->repository->create($request->validated());
        return $this->createdResponse($appointment);
    }

    /**
     * Display the specified appointment.
     * 
     * @param int|string $id The appointment ID.
     * @return JsonResponse The appointment with relations.
     */
    public function show(int|string $id): JsonResponse
    {
        $appointment = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($appointment);
    }

    /**
     * Update the status of the specified appointment.
     * 
     * @param UpdateAppointmentStatusRequest $request The validated request containing the new status.
     * @param int|string $id The appointment ID.
     * @return JsonResponse The updated appointment.
     */
    public function updateStatus(UpdateAppointmentStatusRequest $request, int|string $id): JsonResponse
    {
        $appointment = $this->repository->updateStatus($id, $request->status);
        return $this->successResponse($appointment, 'Appointment status updated successfully');
    }

    /**
     * Remove (cancel) the specified appointment from storage.
     * 
     * @param int|string $id The appointment ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete($id);
        return $this->noContentResponse();
    }
}
