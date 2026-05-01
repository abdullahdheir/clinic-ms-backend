<?php

namespace App\Repositories;

use App\Models\MedicalFile;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;

class MedicalFileRepository extends BaseRepository
{
    /**
     * MedicalFileRepository constructor
     *
     * @param MedicalFile $model
     */
    public function __construct(MedicalFile $model)
    {
        parent::__construct($model);
    }

    /**
     * Get medical files with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithRelations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with(['patient', 'visit'])->get();
    }

    /**
     * Find medical file with relationships
     *
     * @param int|string $id
     * @return MedicalFile|null
     */
    public function findWithRelations(int|string $id): ?MedicalFile
    {
        return $this->model->with(['patient', 'visit'])->find($id);
    }

    /**
     * Find medical file with relationships or throw exception
     *
     * @param int|string $id
     * @return MedicalFile
     */
    public function findWithRelationsOrFail(int|string $id): MedicalFile
    {
        return $this->model->with(['patient', 'visit'])->findOrFail($id);
    }

    /**
     * Delete medical file and its physical file
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteWithFile(int|string $id): bool
    {
        $model = $this->findOrFail($id);
        
        if (Storage::disk('public')->exists($model->file_path)) {
            Storage::disk('public')->delete($model->file_path);
        }
        
        return $model->delete();
    }
}
