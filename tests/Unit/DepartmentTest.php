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
        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'max_capacity' => 20,
            'description' => 'Test Description',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Test Department',
        ]);

        $this->assertEquals('Test Department', $department->name);
        $this->assertEquals(20, $department->max_capacity);
    }

    /**
     * اختبار تحديث بيانات القسم
     */
    public function test_can_update_department(): void
    {
        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
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
        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'max_capacity' => 20,
        ]);

        $departmentId = $department->id;
        $department->delete();

        $this->assertDatabaseMissing('departments', [
            'id' => $departmentId,
        ]);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Clinics
     */
    public function test_department_has_clinics_relationship(): void
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
            'max_capacity' => 20,
        ]);

        $department->clinics()->attach($clinic->id, [
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $department->clinics());
        $this->assertCount(1, $department->clinics);
        $this->assertEquals($clinic->id, $department->clinics->first()->id);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Doctors
     */
    public function test_department_has_doctors_relationship(): void
    {
        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
            'max_capacity' => 20,
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

        $department->doctors()->attach($doctor->id, [
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $department->doctors());
        $this->assertCount(1, $department->doctors);
        $this->assertEquals($doctor->id, $department->doctors->first()->id);
    }
}
