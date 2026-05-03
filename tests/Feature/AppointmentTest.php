<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
        Role::create(['name' => 'receptionist', 'guard_name' => 'web']);
    }

    /**
     * Test that a patient can create an appointment.
     * 
     * @return void
     */
    public function test_patient_can_book_appointment(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');
        
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create(['department_id' => null]); // Simplified for test

        $response = $this->actingAs($patient)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'reason' => 'Routine checkup',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.reason', 'Routine checkup');
    }

    /**
     * Test that we can get today's appointments.
     * 
     * @return void
     */
    public function test_can_get_today_appointments(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        
        Appointment::factory()->create([
            'scheduled_at' => now()->setHour(10)->setMinute(0),
        ]);
        
        Appointment::factory()->create([
            'scheduled_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($doctorUser)->getJson('/api/appointments/today');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test that appointment status can be updated.
     * 
     * @return void
     */
    public function test_can_update_appointment_status(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $receptionist->assignRole('receptionist');
        
        $appointment = Appointment::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($receptionist)->patchJson("/api/appointments/{$appointment->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'confirmed');
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }
}
