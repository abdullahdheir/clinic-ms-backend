<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء مستخدم جديد
     */
    public function test_can_create_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'phone' => '1234567890',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'patient',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('patient', $user->role);
    }

    /**
     * اختبار تحديث بيانات المستخدم
     */
    public function test_can_update_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $user->update([
            'name' => 'Updated User',
            'phone' => '9876543210',
        ]);

        $this->assertEquals('Updated User', $user->name);
        $this->assertEquals('9876543210', $user->phone);
    }

    /**
     * اختبار حذف المستخدم
     */
    public function test_can_delete_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $userId = $user->id;
        $user->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    /**
     * اختبار التحقق من الدور (Super Admin)
     */
    public function test_is_super_admin(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $user->assignRole('super_admin');

        $this->assertTrue($user->isSuperAdmin());
    }

    /**
     * اختبار التحقق من الدور (Manager)
     */
    public function test_is_manager(): void
    {
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $user->assignRole('manager');

        $this->assertTrue($user->isManager());
    }

    /**
     * اختبار التحقق من الدور (Doctor)
     */
    public function test_is_doctor(): void
    {
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $user->assignRole('doctor');

        $this->assertTrue($user->isDoctor());
    }

    /**
     * اختبار التحقق من الدور (Receptionist)
     */
    public function test_is_receptionist(): void
    {
        $user = User::create([
            'name' => 'Receptionist User',
            'email' => 'receptionist@example.com',
            'password' => bcrypt('password'),
            'role' => 'receptionist',
        ]);

        $user->assignRole('receptionist');

        $this->assertTrue($user->isReceptionist());
    }

    /**
     * اختبار التحقق من الدور (Patient)
     */
    public function test_is_patient(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $user->assignRole('patient');

        $this->assertTrue($user->isPatient());
    }

    /**
     * اختبار العلاقة مع Doctor
     */
    public function test_user_has_doctor_relationship(): void
    {
        $user = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $user->doctor());
    }

    /**
     * اختبار العلاقة مع Appointments
     */
    public function test_user_has_appointments_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->appointments());
    }

    /**
     * اختبار العلاقة مع MedicalRecord
     */
    public function test_user_has_medical_record_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $user->medicalRecord());
    }

    /**
     * اختبار العلاقة مع Prescriptions
     */
    public function test_user_has_prescriptions_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->prescriptions());
    }

    /**
     * اختبار العلاقة مع MedicalReports
     */
    public function test_user_has_medical_reports_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->medicalReports());
    }

    /**
     * اختبار العلاقة مع DoctorNotes
     */
    public function test_user_has_doctor_notes_relationship(): void
    {
        $user = User::create([
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->doctorNotes());
    }
}
