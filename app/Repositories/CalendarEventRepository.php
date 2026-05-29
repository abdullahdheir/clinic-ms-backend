<?php

namespace App\Repositories;

use App\Models\CalendarEvent;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class CalendarEventRepository extends BaseRepository
{
    /**
     * CalendarEventRepository constructor.
     *
     * @param CalendarEvent $model The calendar event model instance.
     */
    public function __construct(CalendarEvent $model)
    {
        parent::__construct($model);
    }

    /**
     * Get events within a date range with optional filters.
     *
     * @param string $start Start date.
     * @param string $end End date.
     * @param int|null $doctorId Doctor filter.
     * @param int|null $clinicId Clinic filter.
     * @return Collection List of calendar events.
     */
    public function getBetweenDates(
        string $start,
        string $end,
        ?int $doctorId = null,
        ?int $clinicId = null
    ): Collection {
        $query = $this->model
            ->with(['doctor.user', 'appointment.patient.user'])
            ->whereBetween('start_time', [$start, $end]);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        return $query->get();
    }

    /**
     * Find event by ID with all relationships.
     *
     * @param int $id The event ID.
     * @return CalendarEvent|null
     */
    public function findWithRelations(int $id): ?CalendarEvent
    {
        return $this->model
            ->with(['doctor.user', 'appointment.patient.user'])
            ->find($id);
    }

    /**
     * Get appointments as calendar events format.
     *
     * @param string $start Start date.
     * @param string $end End date.
     * @return Collection List of appointments formatted as events.
     */
    public function getAppointmentsAsEvents(string $start, string $end): Collection
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        return Appointment::with(['patient.user', 'doctor.user'])
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Create event from appointment.
     *
     * @param Appointment $appointment The appointment.
     * @return CalendarEvent The created event.
     */
    public function createFromAppointment(Appointment $appointment): CalendarEvent
    {
        $duration = $appointment->doctor?->session_duration_minutes ?? 30;
        $endTime = Carbon::parse($appointment->scheduled_at)->addMinutes($duration);

        return $this->create([
            'appointment_id' => $appointment->id,
            'title' => $appointment->patient?->name ?? 'موعد',
            'start_time' => $appointment->scheduled_at,
            'end_time' => $endTime,
            'event_type' => 'appointment',
            'doctor_id' => $appointment->doctor_id,
            'color' => $this->getStatusColor($appointment->status),
        ]);
    }

    /**
     * Update appointment scheduled time.
     *
     * @param int $appointmentId The appointment ID.
     * @param string $scheduledAt New scheduled time.
     * @return Appointment The updated appointment.
     */
    public function updateAppointmentTime(int $appointmentId, string $scheduledAt): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $appointment->update(['scheduled_at' => $scheduledAt]);

        // Update associated calendar event if exists
        $event = $this->model->where('appointment_id', $appointmentId)->first();
        if ($event) {
            $duration = $appointment->doctor?->session_duration_minutes ?? 30;
            $endTime = Carbon::parse($scheduledAt)->addMinutes($duration);

            $event->update([
                'start_time' => $scheduledAt,
                'end_time' => $endTime,
            ]);
        }

        return $appointment;
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
