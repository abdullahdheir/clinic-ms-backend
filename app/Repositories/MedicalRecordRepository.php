<?php

namespace App\Repositories;

use App\Models\MedicalRecord;
use App\Repositories\BaseRepository;

class MedicalRecordRepository extends BaseRepository
{
    /**
     * MedicalRecordRepository constructor
     *
     * @param MedicalRecord $model
     */
    public function __construct(MedicalRecord $model)
    {
        parent::__construct($model);
    }

    /**
     * Get medical records with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['patient', 'doctor.user'])->get();
    }

    /**
     * Find medical record with relationships
     *
     * @param int|string $id
     * @return MedicalRecord|null
     */
    public function findWithRelations(int|string $id): ?MedicalRecord
    {
        return $this->model->with(['patient', 'doctor.user'])->find($id);
    }

    /**
     * Find medical record with relationships or throw exception
     *
     * @param int|string $id
     * @return MedicalRecord
     */
    public function findWithRelationsOrFail(int|string $id): MedicalRecord
    {
        return $this->model->with(['patient', 'doctor.user'])->findOrFail($id);
    }
}
