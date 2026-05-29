<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PatientRepository extends BaseRepository
{
    /**
     * PatientRepository constructor.
     *
     * @param Patient $model The patinet model instance.
     */
    public function __construct(Patient $model)
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
        return $this->model->get();
    }

    /**
     * Get a specific patient by ID.
     *
     * @param int|string $id The patient ID.
     * @return Patient|null The patient model instance.
     */
    public function findPatient(int|string $id): ?Patient
    {
        return $this->model->find($id);
    }

    /**
     * Create a new patient (user with role patient).
     *
     * @param array $data The patient data.
     * @return Patient The created patient.
     */
    public function createPatient(array $data): Patient
    {
        $userData = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => isset($data['password']) ? \Illuminate\Support\Facades\Hash::make($data['password']) : \Illuminate\Support\Facades\Hash::make('password123'),
            'national_id' => $data['national_id'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];

        $user = User::create($userData);
        $user->assignRole('patient');

        // Create patient record
        $patient = $this->create([
            'user_id' => $user->id,
        ]);

        // Create empty medical record
        $patient->medicalRecord()->create([
            'blood_type' => $data['blood_type'] ?? null,
        ]);

        return $patient->load(['user', 'medicalRecord']);
    }

    /**
     * Find patient or throw exception.
     *
     * @param int|string $id The patient ID.
     * @return Patient
     */
    public function findPatientOrFail(int|string $id): Patient
    {
        return $this->model->with(['user', 'medicalRecord', 'appointments'])->findOrFail($id);
    }
}
