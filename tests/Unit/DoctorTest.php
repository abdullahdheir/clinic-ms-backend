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
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => 'Cardiologist',
            'bio' => 'Test Bio',
        ]);

        $this->assertDatabaseHas('doctors', [
            'user_id' => $user->id,
        ]);

        $this->assertEquals('Cardiologist', $doctor->specialization);
    }

    /**
     * اختبار تحديث بيانات الطبيب
     */
    public function test_can_update_doctor(): void
    {
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => 'Cardiologist',
            'bio' => 'Test Bio',
        ]);

        $doctor->update([
            'specialization' => 'Neurologist',
            'bio' => 'Updated Bio',
        ]);

        $this->assertEquals('Neurologist', $doctor->specialization);
        $this->assertEquals('Updated Bio', $doctor->bio);
    }

    /**
     * اختبار حذف الطبيب
     */
    public function test_can_delete_doctor(): void
    {
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
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
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => 'Cardiologist',
        ]);

        $this->assertInstanceOf(User::class, $doctor->user);
        $this->assertEquals($user->id, $doctor->user->id);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Clinics
     */
    public function test_doctor_has_clinics_relationship(): void
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

        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => 'Cardiologist',
        ]);

        $doctor->clinics()->attach($clinic->id, [
            'consultation_fee' => 100,
            'session_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $doctor->clinics());
        $this->assertCount(1, $doctor->clinics);
        $this->assertEquals($clinic->id, $doctor->clinics->first()->id);
    }

    /**
     * اختبار العلاقة Many-to-Many مع Departments
     */
    public function test_doctor_has_departments_relationship(): void
    {
        $department = Department::create([
            'name' => 'Test Department',
            'specialty' => 'Cardiology',
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
            'specialization' => 'Cardiologist',
        ]);

        $doctor->departments()->attach($department->id, [
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $doctor->departments());
        $this->assertCount(1, $doctor->departments);
        $this->assertEquals($department->id, $doctor->departments->first()->id);
    }

    /**
     * اختبار العلاقة مع Appointments
     */
    public function test_doctor_has_appointments_relationship(): void
    {
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => 'Cardiologist',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $doctor->appointments());
    }
}
