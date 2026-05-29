<?php

namespace Tests\Unit;

use App\Models\MedicalReport;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء تقرير طبي جديد
     */
    public function test_can_create_medical_report(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('medical_reports', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'report_type' => 'lab',
        ]);

        $this->assertEquals('Test Report', $report->title);
    }

    /**
     * اختبار تحديث بيانات التقرير الطبي
     */
    public function test_can_update_medical_report(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $report->update([
            'title' => 'Updated Report',
            'status' => 'completed',
            'results' => 'Test Results',
        ]);

        $this->assertEquals('Updated Report', $report->title);
        $this->assertEquals('completed', $report->status);
    }

    /**
     * اختبار تغيير حالة التقرير الطبي
     */
    public function test_can_change_medical_report_status(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $report->update(['status' => 'completed']);
        $this->assertEquals('completed', $report->status);

        $report->update(['status' => 'reviewed']);
        $this->assertEquals('reviewed', $report->status);
    }

    /**
     * اختبار حذف التقرير الطبي
     */
    public function test_can_delete_medical_report(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $reportId = $report->id;
        $report->delete();

        $this->assertDatabaseMissing('medical_reports', [
            'id' => $reportId,
        ]);
    }

    /**
     * اختبار العلاقة مع Patient
     */
    public function test_medical_report_has_patient_relationship(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $report->patient);
        $this->assertEquals($patient->id, $report->patient->id);
    }

    /**
     * اختبار العلاقة مع Doctor
     */
    public function test_medical_report_has_doctor_relationship(): void
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

        $report = MedicalReport::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test Report',
            'description' => 'Test Description',
            'report_type' => 'lab',
            'report_date' => now(),
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Doctor::class, $report->doctor);
        $this->assertEquals($doctor->id, $report->doctor->id);
    }
}
