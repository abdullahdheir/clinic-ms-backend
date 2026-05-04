<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PatientRepository extends BaseRepository
{
    /**
     * PatientRepository constructor.
     * 
     * @param User $model The user model instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all users with patient role.
     * 
     * @return Collection List of patients.
     */
    public function allPatients(): Collection
    {
        return $this->model->where('role', 'patient')->get();
    }

    /**
     * Get a specific patient by ID.
     * 
     * @param int|string $id The patient ID.
     * @return User|null The patient model instance.
     */
    public function findPatient(int|string $id): ?User
    {
        return $this->model->where('role', 'patient')->find($id);
    }

    /**
     * Create a new patient (user with role patient).
     * 
     * @param array $data The patient data.
     * @return User The created patient.
     */
    public function createPatient(array $data): User
    {
        $data['role'] = 'patient';
        if (isset($data['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        } else {
            $data['password'] = \Illuminate\Support\Facades\Hash::make('password123'); // Default password
        }

        $user = $this->create($data);
        $user->assignRole('patient');

        // Create empty medical record
        $user->medicalRecord()->create([
            'blood_type' => $data['blood_type'] ?? null,
        ]);

        return $user->load('medicalRecord');
    }

    /**
     * Find patient or throw exception.
     * 
     * @param int|string $id The patient ID.
     * @return User
     */
    public function findPatientOrFail(int|string $id): User
    {
        return $this->model->where('role', 'patient')->with(['medicalRecord', 'appointments'])->findOrFail($id);
    }
}
