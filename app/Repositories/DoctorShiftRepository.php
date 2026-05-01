<?php

namespace App\Repositories;

use App\Models\DoctorShift;
use App\Repositories\BaseRepository;

class DoctorShiftRepository extends BaseRepository
{
    /**
     * DoctorShiftRepository constructor
     *
     * @param DoctorShift $model
     */
    public function __construct(DoctorShift $model)
    {
        parent::__construct($model);
    }

    /**
     * Get doctor shifts with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with('doctor.user')->get();
    }

    /**
     * Find doctor shift with relationships
     *
     * @param int|string $id
     * @return DoctorShift|null
     */
    public function findWithRelations(int|string $id): ?DoctorShift
    {
        return $this->model->with('doctor.user')->find($id);
    }

    /**
     * Find doctor shift with relationships or throw exception
     *
     * @param int|string $id
     * @return DoctorShift
     */
    public function findWithRelationsOrFail(int|string $id): DoctorShift
    {
        return $this->model->with('doctor.user')->findOrFail($id);
    }
}
