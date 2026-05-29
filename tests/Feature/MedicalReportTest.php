<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class MedicalReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
    }

    /**
     * Test that medical reports can be listed for a patient.
     *
     * @return void
     */
    public function test_can_list_medical_reports_for_patient(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        MedicalReport::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/medical-reports?patient_id={$patient->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test that a medical report can be created.
     *
     * @return void
     */
    public function test_can_create_medical_report(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $response = $this->actingAs($doctorUser)->postJson('/api/medical-reports', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Blood Test Results',
            'description' => 'Complete blood count analysis',
            'report_type' => 'blood_test',
            'report_date' => now()->format('Y-m-d'),
            'results' => 'All values within normal range',
            'recommendations' => 'Continue regular checkups',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Blood Test Results');

        $this->assertDatabaseHas('medical_reports', [
            'patient_id' => $patient->id,
            'title' => 'Blood Test Results',
            'report_type' => 'blood_test',
        ]);
    }

    /**
     * Test that a medical report can be shown.
     *
     * @return void
     */
    public function test_can_show_medical_report(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $report = MedicalReport::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'X-Ray Report',
            'description' => 'Chest X-Ray analysis',
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/medical-reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'X-Ray Report')
            ->assertJsonPath('data.description', 'Chest X-Ray analysis');
    }

    /**
     * Test that a medical report can be updated.
     *
     * @return void
     */
    public function test_can_update_medical_report(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $report = MedicalReport::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Old Title',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($doctorUser)->patchJson("/api/medical-reports/{$report->id}", [
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('medical_reports', [
            'id' => $report->id,
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);
    }

    /**
     * Test that a medical report can be deleted.
     *
     * @return void
     */
    public function test_can_delete_medical_report(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $report = MedicalReport::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->deleteJson("/api/medical-reports/{$report->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('medical_reports', [
            'id' => $report->id,
        ]);
    }

    /**
     * Test that report types can be retrieved.
     *
     * @return void
     */
    public function test_can_get_report_types(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');

        $response = $this->actingAs($doctorUser)->getJson('/api/medical-reports/types');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', 'blood_test')
            ->assertJsonPath('data.1.id', 'urine_test');
    }

    /**
     * Test that validation fails when creating report without required fields.
     *
     * @return void
     */
    public function test_validation_fails_for_invalid_medical_report_data(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');

        $response = $this->actingAs($doctorUser)->postJson('/api/medical-reports', [
            'title' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['patient_id', 'doctor_id', 'title', 'description', 'report_type', 'report_date']);
    }
}
