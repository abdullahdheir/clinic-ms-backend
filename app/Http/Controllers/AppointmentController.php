<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentStatusRequest;
use App\Repositories\AppointmentRepository;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorShift;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    /**
     * Get available slots for a doctor on a specific date.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
        ]);

        $doctorId = $request->doctor_id;
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)

        $doctor = Doctor::findOrFail($doctorId);
        
        $shift = DoctorShift::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$shift) {
            return $this->successResponse([]);
        }

        $sessionDuration = $doctor->session_duration_minutes ?? 30;

        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        $slots = [];
        $current = $startTime->copy();

        while ($current->copy()->addMinutes($sessionDuration) <= $endTime) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($sessionDuration);

            $isBooked = $appointments->contains(function ($appointment) use ($slotStart, $slotEnd, $doctor) {
                $aptStart = Carbon::parse($appointment->scheduled_at);
                $aptEnd = $aptStart->copy()->addMinutes($doctor->session_duration_minutes ?? 30);
                return ($slotStart < $aptEnd && $slotEnd > $aptStart);
            });

            $slots[] = [
                'time' => $slotStart->format('H:i'),
                'is_available' => !$isBooked
            ];

            $current->addMinutes($sessionDuration);
        }

        return $this->successResponse($slots);
    }
}
