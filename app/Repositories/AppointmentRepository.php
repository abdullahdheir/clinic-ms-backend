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
}
