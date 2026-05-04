<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorShift;
use Carbon\Carbon;
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

    /**
     * Test getting available slots for a doctor.
     * 
     * @return void
     */
    public function test_can_get_available_slots(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        $doctor = Doctor::factory()->create(['session_duration_minutes' => 30]);
        
        $date = Carbon::tomorrow();
        
        $dayOfWeekInt = $date->dayOfWeek;
        $daysMap = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];
        $dayOfWeekString = $daysMap[$dayOfWeekInt];

        // Create a shift for tomorrow
        DoctorShift::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $dayOfWeekString,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ]);

        // Create an appointment blocking 09:30
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'scheduled_at' => $date->copy()->setTime(9, 30, 0)->format('Y-m-d H:i:s'),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($patient)->getJson('/api/appointments/available-slots?doctor_id=' . $doctor->id . '&date=' . $date->format('Y-m-d'));

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // 09:00 to 12:00 = 3 hours = 6 slots of 30 mins
        // 1 is booked, so 5 should be available
        $this->assertCount(6, $data);
        
        $this->assertTrue($data[0]['is_available']); // 09:00
        $this->assertFalse($data[1]['is_available']); // 09:30
        $this->assertTrue($data[2]['is_available']); // 10:00
    }
}
