<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء مريض جديد
     */
    public function test_can_create_patient(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'blood_type' => 'A+',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '1234567890',
        ]);

        $this->assertDatabaseHas('patients', [
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertEquals('male', $patient->gender);
        $this->assertEquals('A+', $patient->blood_type);
    }

    /**
     * اختبار تحديث بيانات المريض
     */
    public function test_can_update_patient(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'blood_type' => 'A+',
        ]);

        $patient->update([
            'blood_type' => 'B+',
            'insurance_company' => 'Test Insurance',
        ]);

        $this->assertEquals('B+', $patient->blood_type);
        $this->assertEquals('Test Insurance', $patient->insurance_company);
    }

    /**
     * اختبار حذف المريض
     */
    public function test_can_delete_patient(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $patientId = $patient->id;
        $patient->delete();

        $this->assertDatabaseMissing('patients', [
            'id' => $patientId,
        ]);
    }

    /**
     * اختبار العلاقة مع User
     */
    public function test_patient_has_user_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertInstanceOf(User::class, $patient->user);
        $this->assertEquals($user->id, $patient->user->id);
    }

    /**
     * اختبار العلاقة مع Appointments
     */
    public function test_patient_has_appointments_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $patient->appointments());
    }

    /**
     * اختبار العلاقة مع Prescriptions
     */
    public function test_patient_has_prescriptions_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $patient->prescriptions());
    }

    /**
     * اختبار العلاقة مع MedicalReports
     */
    public function test_patient_has_medical_reports_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $patient->medicalReports());
    }

    /**
     * اختبار العلاقة مع DoctorNotes
     */
    public function test_patient_has_doctor_notes_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $patient->doctorNotes());
    }
}
