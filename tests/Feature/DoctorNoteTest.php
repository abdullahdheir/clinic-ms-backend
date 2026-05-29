<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class DoctorNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
    }

    /**
     * Test that doctor notes can be listed for a patient.
     *
     * @return void
     */
    public function test_can_list_doctor_notes_for_patient(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        DoctorNote::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/doctor-notes?patient_id={$patient->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test that a doctor note can be created.
     *
     * @return void
     */
    public function test_can_create_doctor_note(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $response = $this->actingAs($doctorUser)->postJson('/api/doctor-notes', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Patient showing good progress',
            'note_type' => 'follow_up',
            'is_pinned' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.content', 'Patient showing good progress')
            ->assertJsonPath('data.note_type', 'follow_up')
            ->assertJsonPath('data.is_pinned', true);

        $this->assertDatabaseHas('doctor_notes', [
            'patient_id' => $patient->id,
            'content' => 'Patient showing good progress',
            'note_type' => 'follow_up',
            'is_pinned' => true,
        ]);
    }

    /**
     * Test that a doctor note can be shown.
     *
     * @return void
     */
    public function test_can_show_doctor_note(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $note = DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test note content',
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/doctor-notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'Test note content');
    }

    /**
     * Test that a doctor note can be updated.
     *
     * @return void
     */
    public function test_can_update_doctor_note(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $note = DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Old content',
            'note_type' => 'general',
        ]);

        $response = $this->actingAs($doctorUser)->patchJson("/api/doctor-notes/{$note->id}", [
            'content' => 'Updated content',
            'note_type' => 'urgent',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'Updated content')
            ->assertJsonPath('data.note_type', 'urgent');

        $this->assertDatabaseHas('doctor_notes', [
            'id' => $note->id,
            'content' => 'Updated content',
            'note_type' => 'urgent',
        ]);
    }

    /**
     * Test that a doctor note can be deleted.
     *
     * @return void
     */
    public function test_can_delete_doctor_note(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $note = DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->deleteJson("/api/doctor-notes/{$note->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('doctor_notes', [
            'id' => $note->id,
        ]);
    }

    /**
     * Test that a doctor note can be pinned/unpinned.
     *
     * @return void
     */
    public function test_can_toggle_pin_doctor_note(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $note = DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($doctorUser)->postJson("/api/doctor-notes/{$note->id}/toggle-pin");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_pinned', true);

        $this->assertDatabaseHas('doctor_notes', [
            'id' => $note->id,
            'is_pinned' => true,
        ]);
    }

    /**
     * Test that pinned notes appear first in the list.
     *
     * @return void
     */
    public function test_pinned_notes_appear_first(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'is_pinned' => false,
            'created_at' => now()->subDay(),
        ]);

        $pinnedNote = DoctorNote::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'is_pinned' => true,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/doctor-notes?patient_id={$patient->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.is_pinned', true);
    }
}
