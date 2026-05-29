<?php

namespace Tests\Unit;

use App\Models\DoctorNote;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorNoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء ملاحظة طبيب جديدة
     */
    public function test_can_create_doctor_note(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $this->assertDatabaseHas('doctor_notes', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'note_type' => 'general',
        ]);

        $this->assertEquals('Test Note Content', $note->content);
    }

    /**
     * اختبار تحديث بيانات ملاحظة الطبيب
     */
    public function test_can_update_doctor_note(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $note->update([
            'content' => 'Updated Note Content',
            'note_type' => 'urgent',
            'is_pinned' => true,
        ]);

        $this->assertEquals('Updated Note Content', $note->content);
        $this->assertEquals('urgent', $note->note_type);
        $this->assertTrue($note->is_pinned);
    }

    /**
     * اختبار تثبيت/إلغاء تثبيت ملاحظة الطبيب
     */
    public function test_can_toggle_pin_doctor_note(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $note->update(['is_pinned' => true]);
        $this->assertTrue($note->is_pinned);

        $note->update(['is_pinned' => false]);
        $this->assertFalse($note->is_pinned);
    }

    /**
     * اختبار حذف ملاحظة الطبيب
     */
    public function test_can_delete_doctor_note(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $noteId = $note->id;
        $note->delete();

        $this->assertDatabaseMissing('doctor_notes', [
            'id' => $noteId,
        ]);
    }

    /**
     * اختبار العلاقة مع Patient
     */
    public function test_doctor_note_has_patient_relationship(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $this->assertInstanceOf(User::class, $note->patient);
        $this->assertEquals($patient->id, $note->patient->id);
    }

    /**
     * اختبار العلاقة مع Doctor
     */
    public function test_doctor_note_has_doctor_relationship(): void
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

        $note = DoctorNote::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'content' => 'Test Note Content',
            'note_type' => 'general',
            'is_pinned' => false,
        ]);

        $this->assertInstanceOf(Doctor::class, $note->doctor);
        $this->assertEquals($doctor->id, $note->doctor->id);
    }
}
