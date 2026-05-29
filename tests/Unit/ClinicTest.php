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
     * اختبار العلاقة مع Departments
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

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $clinic->departments());
    }

    /**
     * اختبار العلاقة مع Doctors
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

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $clinic->doctors());
    }
}
