<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionMedication;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
    }

    /**
     * Test that prescriptions can be listed for a patient.
     *
     * @return void
     */
    public function test_can_list_prescriptions_for_patient(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        Prescription::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/prescriptions?patient_id={$patient->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test that a prescription can be created.
     *
     * @return void
     */
    public function test_can_create_prescription(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $response = $this->actingAs($doctorUser)->postJson('/api/prescriptions', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Hypertension',
            'notes' => 'Take medication regularly',
            'prescription_date' => now()->format('Y-m-d'),
            'medications' => [
                [
                    'medication_name' => 'Amlodipine',
                    'dosage' => '5mg',
                    'frequency' => 'Once daily',
                    'duration' => '30 days',
                    'instructions' => 'Take in the morning',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.diagnosis', 'Hypertension');

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'diagnosis' => 'Hypertension',
        ]);
    }

    /**
     * Test that a prescription can be shown.
     *
     * @return void
     */
    public function test_can_show_prescription(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Test Diagnosis',
        ]);

        PrescriptionMedication::factory()->create([
            'prescription_id' => $prescription->id,
            'medication_name' => 'Test Medication',
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/prescriptions/{$prescription->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnosis', 'Test Diagnosis')
            ->assertJsonPath('data.medications.0.medication_name', 'Test Medication');
    }

    /**
     * Test that a prescription can be updated.
     *
     * @return void
     */
    public function test_can_update_prescription(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Old Diagnosis',
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctorUser)->patchJson("/api/prescriptions/{$prescription->id}", [
            'diagnosis' => 'Updated Diagnosis',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnosis', 'Updated Diagnosis')
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'diagnosis' => 'Updated Diagnosis',
            'status' => 'completed',
        ]);
    }

    /**
     * Test that a prescription can be deleted.
     *
     * @return void
     */
    public function test_can_delete_prescription(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->deleteJson("/api/prescriptions/{$prescription->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('prescriptions', [
            'id' => $prescription->id,
        ]);
    }

    /**
     * Test that validation fails when creating prescription without required fields.
     *
     * @return void
     */
    public function test_validation_fails_for_invalid_prescription_data(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');

        $response = $this->actingAs($doctorUser)->postJson('/api/prescriptions', [
            'diagnosis' => '', // Empty diagnosis
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['patient_id', 'doctor_id', 'diagnosis', 'prescription_date', 'medications']);
    }
}
