<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Repositories\BaseRepository;

class DoctorRepository extends BaseRepository
{
    /**
     * DoctorRepository constructor
     *
     * @param Doctor $model
     */
    public function __construct(Doctor $model)
    {
        parent::__construct($model);
    }

    /**
     * Get doctors with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['user', 'department'])->get();
    }

    /**
     * Find doctor with relationships
     *
     * @param int|string $id
     * @return Doctor|null
     */
    public function findWithRelations(int|string $id): ?Doctor
    {
        return $this->model->with(['user', 'department.clinic'])->find($id);
    }

    /**
     * Find doctor with relationships or throw exception
     *
     * @param int|string $id
     * @return Doctor
     */
    public function findWithRelationsOrFail(int|string $id): Doctor
    {
        return $this->model->with(['user', 'department.clinic'])->findOrFail($id);
    }
}
