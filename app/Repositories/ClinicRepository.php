<?php

namespace App\Repositories;

use App\Models\Clinic;
use App\Repositories\BaseRepository;

class ClinicRepository extends BaseRepository
{
    /**
     * ClinicRepository constructor
     *
     * @param Clinic $model
     */
    public function __construct(Clinic $model)
    {
        parent::__construct($model);
    }

    /**
     * Get clinics with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with('manager')->get();
    }

    /**
     * Find clinic with relationships
     *
     * @param int|string $id
     * @return Clinic|null
     */
    public function findWithRelations(int|string $id): ?Clinic
    {
        return $this->model->with(['manager', 'departments'])->find($id);
    }

    /**
     * Find clinic with relationships or throw exception
     *
     * @param int|string $id
     * @return Clinic
     */
    public function findWithRelationsOrFail(int|string $id): Clinic
    {
        return $this->model->with(['manager', 'departments'])->findOrFail($id);
    }
}
