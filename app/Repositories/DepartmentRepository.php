<?php

namespace App\Repositories;

use App\Models\Department;
use App\Repositories\BaseRepository;

class DepartmentRepository extends BaseRepository
{
    /**
     * DepartmentRepository constructor
     *
     * @param Department $model
     */
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    /**
     * Get departments with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['clinic', 'doctors'])->get();
    }

    /**
     * Find department with relationships
     *
     * @param int|string $id
     * @return Department|null
     */
    public function findWithRelations(int|string $id): ?Department
    {
        return $this->model->with(['clinic', 'doctors.user'])->find($id);
    }

    /**
     * Find department with relationships or throw exception
     *
     * @param int|string $id
     * @return Department
     */
    public function findWithRelationsOrFail(int|string $id): Department
    {
        return $this->model->with(['clinic', 'doctors.user'])->findOrFail($id);
    }
}
