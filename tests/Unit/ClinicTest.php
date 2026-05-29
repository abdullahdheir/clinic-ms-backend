<?php

namespace Tests\Unit;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء عيادة جديدة
     */
    public function test_can_create_clinic(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('clinics', [
            'name' => 'Test Clinic',
            'manager_id' => $manager->id,
        ]);

        $this->assertEquals('Test Clinic', $clinic->name);
        $this->assertTrue($clinic->is_active);
    }

    /**
     * اختبار تحديث بيانات العيادة
     */
    public function test_can_update_clinic(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
        ]);

        $clinic->update([
            'name' => 'Updated Clinic',
            'phone' => '9876543210',
        ]);

        $this->assertEquals('Updated Clinic', $clinic->name);
        $this->assertEquals('9876543210', $clinic->phone);
    }

    /**
     * اختبار حذف العيادة
     */
    public function test_can_delete_clinic(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
        ]);

        $clinicId = $clinic->id;
        $clinic->delete();

        $this->assertDatabaseMissing('clinics', [
            'id' => $clinicId,
        ]);
    }

    /**
     * اختبار تفعيل/تعطيل العيادة
     */
    public function test_can_toggle_clinic_status(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $clinic->update(['is_active' => false]);
        $this->assertFalse($clinic->is_active);

        $clinic->update(['is_active' => true]);
        $this->assertTrue($clinic->is_active);
    }

    /**
     * اختبار العلاقة مع Manager
     */
    public function test_clinic_has_manager_relationship(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
        ]);

        $this->assertInstanceOf(User::class, $clinic->manager);
        $this->assertEquals($manager->id, $clinic->manager->id);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Departments
     */
    public function test_clinic_has_departments_relationship(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
        ]);

        $department = \App\Models\Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'max_capacity' => 20,
        ]);

        $clinic->departments()->attach($department->id, [
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $clinic->departments());
        $this->assertCount(1, $clinic->departments);
        $this->assertEquals($department->id, $clinic->departments->first()->id);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Doctors
     */
    public function test_clinic_has_doctors_relationship(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'manager_id' => $manager->id,
        ]);

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = \App\Models\Doctor::create([
            'user_id' => $doctorUser->id,
            'specialization' => 'Cardiologist',
        ]);

        $clinic->doctors()->attach($doctor->id, [
            'consultation_fee' => 100,
            'session_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $clinic->doctors());
        $this->assertCount(1, $clinic->doctors);
        $this->assertEquals($doctor->id, $clinic->doctors->first()->id);
    }
}
