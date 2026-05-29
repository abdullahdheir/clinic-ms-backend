<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء قسم جديد
     */
    public function test_can_create_department(): void
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

        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'clinic_id' => $clinic->id,
            'max_capacity' => 20,
            'description' => 'Test Description',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Test Department',
            'clinic_id' => $clinic->id,
        ]);

        $this->assertEquals('Test Department', $department->name);
        $this->assertEquals(20, $department->max_capacity);
    }

    /**
     * اختبار تحديث بيانات القسم
     */
    public function test_can_update_department(): void
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

        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'clinic_id' => $clinic->id,
            'max_capacity' => 20,
        ]);

        $department->update([
            'name' => 'Updated Department',
            'specialty' => 'Neurology',
            'max_capacity' => 30,
        ]);

        $this->assertEquals('Updated Department', $department->name);
        $this->assertEquals('Neurology', $department->specialty);
        $this->assertEquals(30, $department->max_capacity);
    }

    /**
     * اختبار حذف القسم
     */
    public function test_can_delete_department(): void
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

        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'clinic_id' => $clinic->id,
            'max_capacity' => 20,
        ]);

        $departmentId = $department->id;
        $department->delete();

        $this->assertDatabaseMissing('departments', [
            'id' => $departmentId,
        ]);
    }

    /**
     * اختبار العلاقة مع Clinic
     */
    public function test_department_has_clinic_relationship(): void
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

        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'clinic_id' => $clinic->id,
            'max_capacity' => 20,
        ]);

        $this->assertInstanceOf(Clinic::class, $department->clinic);
        $this->assertEquals($clinic->id, $department->clinic->id);
    }

    /**
     * اختبار العلاقة مع Doctors
     */
    public function test_department_has_doctors_relationship(): void
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

        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'clinic_id' => $clinic->id,
            'max_capacity' => 20,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $department->doctors());
    }
}
