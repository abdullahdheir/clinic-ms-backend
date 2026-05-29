<?php

namespace Tests\Unit;

use App\Models\Prescription;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء وصفة طبية جديدة
     */
    public function test_can_create_prescription(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
            'notes' => 'Test Notes',
        ]);

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'active',
        ]);

        $this->assertEquals('Test Diagnosis', $prescription->diagnosis);
    }

    /**
     * اختبار تحديث بيانات الوصفة الطبية
     */
    public function test_can_update_prescription(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
        ]);

        $prescription->update([
            'status' => 'completed',
            'diagnosis' => 'Updated Diagnosis',
            'notes' => 'Updated Notes',
        ]);

        $this->assertEquals('completed', $prescription->status);
        $this->assertEquals('Updated Diagnosis', $prescription->diagnosis);
    }

    /**
     * اختبار تغيير حالة الوصفة الطبية
     */
    public function test_can_change_prescription_status(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
        ]);

        $prescription->update(['status' => 'completed']);
        $this->assertEquals('completed', $prescription->status);

        $prescription->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $prescription->status);
    }

    /**
     * اختبار حذف الوصفة الطبية
     */
    public function test_can_delete_prescription(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
        ]);

        $prescriptionId = $prescription->id;
        $prescription->delete();

        $this->assertDatabaseMissing('prescriptions', [
            'id' => $prescriptionId,
        ]);
    }

    /**
     * اختبار العلاقة مع Patient
     */
    public function test_prescription_has_patient_relationship(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
        ]);

        $this->assertInstanceOf(User::class, $prescription->patient);
        $this->assertEquals($patient->id, $prescription->patient->id);
    }

    /**
     * اختبار العلاقة مع Doctor
     */
    public function test_prescription_has_doctor_relationship(): void
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

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now(),
            'status' => 'active',
            'diagnosis' => 'Test Diagnosis',
        ]);

        $this->assertInstanceOf(Doctor::class, $prescription->doctor);
        $this->assertEquals($doctor->id, $prescription->doctor->id);
    }
}
