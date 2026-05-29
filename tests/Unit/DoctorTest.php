<?php

namespace Tests\Unit;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء طبيب جديد
     */
    public function test_can_create_doctor(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
            'session_duration_minutes' => 30,
            'consultation_fee' => 100,
            'bio' => 'Test Bio',
        ]);

        $this->assertDatabaseHas('doctors', [
            'user_id' => $user->id,
            'department_id' => $department->id,
        ]);

        $this->assertEquals('Cardiologist', $doctor->specialization);
        $this->assertEquals(30, $doctor->session_duration_minutes);
    }

    /**
     * اختبار تحديث بيانات الطبيب
     */
    public function test_can_update_doctor(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
            'session_duration_minutes' => 30,
            'consultation_fee' => 100,
        ]);

        $doctor->update([
            'specialization' => 'Neurologist',
            'session_duration_minutes' => 45,
            'consultation_fee' => 150,
        ]);

        $this->assertEquals('Neurologist', $doctor->specialization);
        $this->assertEquals(45, $doctor->session_duration_minutes);
        $this->assertEquals(150, $doctor->consultation_fee);
    }

    /**
     * اختبار حذف الطبيب
     */
    public function test_can_delete_doctor(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $doctorId = $doctor->id;
        $doctor->delete();

        $this->assertDatabaseMissing('doctors', [
            'id' => $doctorId,
        ]);
    }

    /**
     * اختبار العلاقة مع User
     */
    public function test_doctor_has_user_relationship(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $this->assertInstanceOf(User::class, $doctor->user);
        $this->assertEquals($user->id, $doctor->user->id);
    }

    /**
     * اختبار العلاقة مع Department
     */
    public function test_doctor_has_department_relationship(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $this->assertInstanceOf(Department::class, $doctor->department);
        $this->assertEquals($department->id, $doctor->department->id);
    }

    /**
     * اختبار العلاقة مع Appointments
     */
    public function test_doctor_has_appointments_relationship(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $doctor->appointments());
    }
}
