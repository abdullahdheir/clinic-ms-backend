<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles for every test
        Role::create(['name' => 'manager', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
    }

    /**
     * Test that a manager can create a clinic.
     * 
     * @return void
     */
    public function test_manager_can_create_clinic(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->postJson('/api/clinics', [
            'name' => 'New Test Clinic',
            'address' => 'Test Address',
            'phone' => '123456789',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Test Clinic');
        
        $this->assertDatabaseHas('clinics', ['name' => 'New Test Clinic']);
    }

    /**
     * Test that a manager can list clinics.
     * 
     * @return void
     */
    public function test_manager_can_list_clinics(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $manager->assignRole('manager');
        Clinic::factory()->count(3)->create(['manager_id' => $manager->id]);

        $response = $this->actingAs($manager)->getJson('/api/clinics');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test that a manager can update a clinic.
     * 
     * @return void
     */
    public function test_manager_can_update_clinic(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $manager->assignRole('manager');
        $clinic = Clinic::factory()->create(['manager_id' => $manager->id]);

        $response = $this->actingAs($manager)->putJson("/api/clinics/{$clinic->id}", [
            'name' => 'Updated Clinic Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Clinic Name');
    }

    /**
     * Test that a doctor cannot create a clinic (should fail with 403).
     * 
     * @return void
     */
    public function test_doctor_cannot_create_clinic(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $doctor->assignRole('doctor');

        $response = $this->actingAs($doctor)->postJson('/api/clinics', [
            'name' => 'Illegal Clinic',
        ]);

        // Assuming you have middleware or policy to restrict this. 
        // If not yet implemented, this might fail.
        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access clinics.
     * 
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_clinics(): void
    {
        $response = $this->getJson('/api/clinics');

        $response->assertStatus(401);
    }
}
