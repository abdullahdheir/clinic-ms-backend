<?php

namespace App\Repositories;

use App\Models\Visit;
use App\Repositories\BaseRepository;

class VisitRepository extends BaseRepository
{
    /**
     * VisitRepository constructor
     *
     * @param Visit $model
     */
    public function __construct(Visit $model)
    {
        parent::__construct($model);
    }

    /**
     * Get visits with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['patient', 'doctor.user', 'appointment'])->get();
    }

    /**
     * Find visit with relationships
     *
     * @param int|string $id
     * @return Visit|null
     */
    public function findWithRelations(int|string $id): ?Visit
    {
        return $this->model->with(['patient', 'doctor.user', 'appointment', 'medicalFiles'])->find($id);
    }

    /**
     * Find visit with relationships or throw exception
     *
     * @param int|string $id
     * @return Visit
     */
    public function findWithRelationsOrFail(int|string $id): Visit
    {
        return $this->model->with(['patient', 'doctor.user', 'appointment', 'medicalFiles'])->findOrFail($id);
    }
}
