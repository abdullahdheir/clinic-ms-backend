<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'manager', 'guard_name' => 'web']);
    }

    /**
     * Test that a manager can create a doctor profile.
     * 
     * @return void
     */
    public function test_manager_can_create_doctor(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $manager->assignRole('manager');
        
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $department = Department::factory()->create();

        $response = $this->actingAs($manager)->postJson('/api/doctors', [
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
            'session_duration_minutes' => 30,
            'consultation_fee' => 150.00,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.specialization', 'Cardiologist');
        
        $this->assertDatabaseHas('doctors', [
            'user_id' => $doctorUser->id,
            'specialization' => 'Cardiologist',
        ]);
    }

    /**
     * Test that we can list doctors.
     * 
     * @return void
     */
    public function test_can_list_doctors(): void
    {
        Doctor::factory()->count(2)->create();

        $user = User::factory()->create(); // Regular user

        $response = $this->actingAs($user)->getJson('/api/doctors');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }
}
