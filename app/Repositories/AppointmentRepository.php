<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\BaseRepository;

class AppointmentRepository extends BaseRepository
{
    /**
     * AppointmentRepository constructor
     *
     * @param Appointment $model
     */
    public function __construct(Appointment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get appointments with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['patient', 'doctor.user', 'department'])->get();
    }

    /**
     * Find appointment with relationships
     *
     * @param int|string $id
     * @return Appointment|null
     */
    public function findWithRelations(int|string $id): ?Appointment
    {
        return $this->model->with(['patient', 'doctor.user', 'department', 'checkedInBy'])->find($id);
    }

    /**
     * Find appointment with relationships or throw exception
     *
     * @param int|string $id
     * @return Appointment
     */
    public function findWithRelationsOrFail(int|string $id): Appointment
    {
        return $this->model->with(['patient', 'doctor.user', 'department', 'checkedInBy'])->findOrFail($id);
    }

    /**
     * Get appointments scheduled for today.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTodayAppointments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereDate('scheduled_at', now()->toDateString())
            ->with(['patient', 'doctor.user', 'department'])
            ->get();
    }

    /**
     * Update appointment status.
     * 
     * @param int|string $id The appointment ID.
     * @param string $status The new status.
     * @return Appointment
     */
    public function updateStatus(int|string $id, string $status): Appointment
    {
        $appointment = $this->findOrFail($id);
        $appointment->update(['status' => $status]);
        return $appointment;
    }
}
