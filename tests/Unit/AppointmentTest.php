<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء موعد جديد
     */
    public function test_can_create_appointment(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
            'notes' => 'Test Notes',
        ]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $appointment->status);
    }

    /**
     * اختبار تحديث بيانات الموعد
     */
    public function test_can_update_appointment(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $appointment->update([
            'status' => 'confirmed',
            'notes' => 'Updated Notes',
        ]);

        $this->assertEquals('confirmed', $appointment->status);
        $this->assertEquals('Updated Notes', $appointment->notes);
    }

    /**
     * اختبار تغيير حالة الموعد
     */
    public function test_can_change_appointment_status(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $appointment->update(['status' => 'confirmed']);
        $this->assertEquals('confirmed', $appointment->status);

        $appointment->update(['status' => 'done']);
        $this->assertEquals('done', $appointment->status);

        $appointment->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $appointment->status);
    }

    /**
     * اختبار حذف الموعد
     */
    public function test_can_delete_appointment(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $appointmentId = $appointment->id;
        $appointment->delete();

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointmentId,
        ]);
    }

    /**
     * اختبار العلاقة مع Patient
     */
    public function test_appointment_has_patient_relationship(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $appointment->patient);
        $this->assertEquals($patient->id, $appointment->patient->id);
    }

    /**
     * اختبار العلاقة مع Doctor
     */
    public function test_appointment_has_doctor_relationship(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Doctor::class, $appointment->doctor);
        $this->assertEquals($doctor->id, $appointment->doctor->id);
    }

    /**
     * اختبار العلاقة مع Clinic
     */
    public function test_appointment_has_clinic_relationship(): void
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

        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'specialization' => 'Cardiologist',
        ]);

        $patient = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Clinic::class, $appointment->clinic);
        $this->assertEquals($clinic->id, $appointment->clinic->id);
    }
}
