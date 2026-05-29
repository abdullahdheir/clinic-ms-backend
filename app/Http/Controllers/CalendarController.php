<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calendar\StoreCalendarEventRequest;
use App\Http\Requests\Calendar\UpdateCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Repositories\CalendarEventRepository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    use ApiResponse;

    /**
     * CalendarController constructor.
     *
     * @param CalendarEventRepository $repository The calendar event repository instance.
     */
    public function __construct(
        private CalendarEventRepository $repository
    ) {}

    /**
     * Display a listing of calendar events.
     *
     * @param Request $request The request containing date range and filters.
     * @return JsonResponse List of calendar events.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
        ]);

        $events = $this->repository->getBetweenDates(
            $request->start,
            $request->end,
            $request->doctor_id ? (int) $request->doctor_id : null,
            $request->clinic_id ? (int) $request->clinic_id : null
        );

        return $this->successResponse(
            CalendarEventResource::collection($events),
            'Calendar events retrieved successfully'
        );
    }

    /**
     * Store a newly created calendar event.
     *
     * @param StoreCalendarEventRequest $request The validated store request.
     * @return JsonResponse The created calendar event.
     */
    public function store(StoreCalendarEventRequest $request): JsonResponse
    {
        try {
            $event = $this->repository->create($request->validated());

            return $this->createdResponse(
                new CalendarEventResource($event),
                'Calendar event created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create event: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified calendar event.
     *
     * @param int|string $id The calendar event ID.
     * @return JsonResponse The calendar event details.
     */
    public function show(int|string $id): JsonResponse
    {
        $event = $this->repository->findWithRelations((int) $id);

        if (!$event) {
            return $this->notFoundResponse('Calendar event');
        }

        return $this->successResponse(
            new CalendarEventResource($event),
            'Calendar event retrieved successfully'
        );
    }

    /**
     * Update the specified calendar event.
     *
     * @param UpdateCalendarEventRequest $request The validated update request.
     * @param int|string $id The calendar event ID.
     * @return JsonResponse The updated calendar event.
     */
    public function update(UpdateCalendarEventRequest $request, int|string $id): JsonResponse
    {
        try {
            $event = $this->repository->update((int) $id, $request->validated());

            return $this->successResponse(
                new CalendarEventResource($event),
                'Calendar event updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update event: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified calendar event.
     *
     * @param int|string $id The calendar event ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete((int) $id);

        return $this->noContentResponse();
    }

    /**
     * Get appointments formatted as calendar events.
     *
     * @param Request $request The request containing date range.
     * @return JsonResponse List of appointments as calendar events.
     */
    public function appointments(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $appointments = $this->repository->getAppointmentsAsEvents(
            $request->start,
            $request->end
        );

        $events = $appointments->map(function ($appointment) {
            return [
                'id' => 'apt_' . $appointment->id,
                'title' => $appointment->patient?->name ?? 'Unknown Patient',
                'start' => $appointment->scheduled_at,
                'end' => Carbon::parse($appointment->scheduled_at)->addMinutes(
                    $appointment->doctor?->session_duration_minutes ?? 30
                ),
                'type' => 'appointment',
                'color' => $this->getStatusColor($appointment->status),
                'extendedProps' => [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                ],
            ];
        });

        return $this->successResponse($events, 'Appointments retrieved successfully');
    }

    /**
     * Update appointment scheduled time (Drag & Drop).
     *
     * @param Request $request The request containing new scheduled time.
     * @param int|string $id The appointment ID.
     * @return JsonResponse The updated appointment.
     */
    public function updateAppointmentTime(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        try {
            $appointment = $this->repository->updateAppointmentTime(
                (int) $id,
                $request->scheduled_at
            );

            return $this->successResponse(
                $appointment,
                'Appointment time updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update appointment time: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get color for appointment status.
     *
     * @param string $status The appointment status.
     * @return string The color hex code.
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => '#F0A500',
            'confirmed' => '#0B6E6E',
            'cancelled' => '#D94F4F',
            'done' => '#2E9E6B',
            'in_progress' => '#3B82C4',
            'no_show' => '#9AA0AE',
        ];

        return $colors[$status] ?? '#6B7280';
    }
}
