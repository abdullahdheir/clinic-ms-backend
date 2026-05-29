<?php

namespace App\Repositories;

use App\Models\Prescription;
use App\Models\PrescriptionMedication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PrescriptionRepository extends BaseRepository
{
    /**
     * PrescriptionRepository constructor.
     *
     * @param Prescription $model The prescription model instance.
     */
    public function __construct(Prescription $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all prescriptions for a patient with relationships.
     *
     * @param int $patientId The patient ID.
     * @return Collection List of prescriptions.
     */
    public function getByPatient(int $patientId): Collection
    {
        return $this->model
            ->with(['doctor.user', 'medications'])
            ->where('patient_id', $patientId)
            ->orderBy('prescription_date', 'desc')
            ->get();
    }

    /**
     * Find prescription by ID with all relationships.
     *
     * @param int $id The prescription ID.
     * @return Prescription|null
     */
    public function findWithRelations(int $id): ?Prescription
    {
        return $this->model
            ->with(['doctor.user', 'patient', 'medications', 'visit'])
            ->find($id);
    }

    /**
     * Create a new prescription with medications.
     *
     * @param array $data The prescription data including medications.
     * @return Prescription The created prescription.
     * @throws \Exception
     */
    public function createWithMedications(array $data): Prescription
    {
        return DB::transaction(function () use ($data) {
            $prescription = $this->create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'diagnosis' => $data['diagnosis'],
                'notes' => $data['notes'] ?? null,
                'prescription_date' => $data['prescription_date'],
                'status' => $data['status'] ?? 'active',
            ]);

            if (isset($data['medications'])) {
                foreach ($data['medications'] as $medication) {
                    PrescriptionMedication::create([
                        'prescription_id' => $prescription->id,
                        'medication_name' => $medication['medication_name'],
                        'dosage' => $medication['dosage'],
                        'frequency' => $medication['frequency'],
                        'duration' => $medication['duration'],
                        'instructions' => $medication['instructions'] ?? null,
                    ]);
                }
            }

            return $prescription->load('medications');
        });
    }

    /**
     * Update prescription and its medications.
     *
     * @param int $id The prescription ID.
     * @param array $data The update data.
     * @return Prescription The updated prescription.
     * @throws \Exception
     */
    public function updateWithMedications(int $id, array $data): Prescription
    {
        return DB::transaction(function () use ($id, $data) {
            $prescription = $this->findOrFail($id);

            $updateData = array_intersect_key($data, array_flip([
                'diagnosis', 'notes', 'status'
            ]));

            if (!empty($updateData)) {
                $prescription->update($updateData);
            }

            if (isset($data['medications'])) {
                foreach ($data['medications'] as $medication) {
                    if (isset($medication['id'])) {
                        PrescriptionMedication::where('id', $medication['id'])
                            ->update([
                                'medication_name' => $medication['medication_name'],
                                'dosage' => $medication['dosage'],
                                'frequency' => $medication['frequency'],
                                'duration' => $medication['duration'],
                                'instructions' => $medication['instructions'] ?? null,
                            ]);
                    }
                }
            }

            return $prescription->fresh()->load('medications');
        });
    }
}
