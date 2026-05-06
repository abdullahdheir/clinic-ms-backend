<?php

namespace Tests\Unit;

use App\Jobs\AppointmentReminderJob;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Notification;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentReminderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_reminder_job_can_be_instantiated()
    {
        $job = new AppointmentReminderJob();
        $this->assertInstanceOf(AppointmentReminderJob::class, $job);
    }

    public function test_job_handles_24_hour_reminders()
    {
        // Create test data
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        
        // Create appointment 24 hours from now
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(24),
            'status' => 'confirmed',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Assert notifications were created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $doctor->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        // Assert emails were queued
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 2);
    }

    public function test_job_handles_2_hour_reminders()
    {
        // Create test data
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        
        // Create appointment 2 hours from now
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Assert notifications were created with high priority
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $doctor->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        // Assert emails were queued
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 2);
    }

    public function test_job_ignores_appointments_outside_time_window()
    {
        // Create test data
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        
        // Create appointment 3 hours from now (outside 24h and 2h window)
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(3),
            'status' => 'confirmed',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Assert no notifications were created
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $patient->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        // Assert no emails were queued
        Mail::assertNotQueued(\App\Mail\AppointmentReminderMail::class);
    }

    public function test_job_ignores_cancelled_appointments()
    {
        // Create test data
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        
        // Create cancelled appointment 2 hours from now
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'cancelled',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Assert no notifications were created
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $patient->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        // Assert no emails were queued
        Mail::assertNotQueued(\App\Mail\AppointmentReminderMail::class);
    }

    public function test_job_creates_notifications_with_correct_data()
    {
        // Create test data
        $clinic = Clinic::factory()->create(['name' => 'Test Clinic']);
        $doctor = Doctor::factory()->create();
        $doctorUser = User::factory()->create();
        $doctor->user_id = $doctorUser->id;
        $doctor->save();
        $patient = User::factory()->create();
        
        // Create appointment 2 hours from now
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Check patient notification data
        $patientNotification = Notification::where('user_id', $patient->id)->first();
        $this->assertNotNull($patientNotification);
        $this->assertStringContainsString('موعدك', $patientNotification->title);
        $this->assertStringContainsString('د.', $patientNotification->message);
        $this->assertEquals('/appointments/' . $appointment->id, $patientNotification->link);
        $this->assertArrayHasKey('appointment_id', $patientNotification->data);
        $this->assertEquals($appointment->id, $patientNotification->data['appointment_id']);

        // Check doctor notification data
        $doctorNotification = Notification::where('user_id', $doctor->user_id)->first();
        $this->assertNotNull($doctorNotification);
        $this->assertStringContainsString('موعد', $doctorNotification->title);
        $this->assertStringContainsString($patient->name, $doctorNotification->message);
    }

    public function test_job_handles_multiple_appointments()
    {
        // Create test data
        $clinic = Clinic::factory()->create();
        $doctor = Doctor::factory()->create();
        $patient1 = User::factory()->create();
        $patient2 = User::factory()->create();
        
        // Create multiple appointments
        Appointment::create([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'scheduled_at' => Carbon::now()->addHours(24),
            'status' => 'confirmed',
        ]);

        // Mock mail to prevent actual sending
        Mail::fake();

        // Run the job
        $job = new AppointmentReminderJob();
        $job->handle();

        // Assert notifications for both patients
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient1->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient2->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        // Assert notifications for doctor
        $doctorNotifications = Notification::where('user_id', $doctor->user_id)
            ->where('type', NotificationType::APPOINTMENT_REMINDER)
            ->get();
        $this->assertCount(2, $doctorNotifications);

        // Assert 4 emails were queued (2 patients + 2 for doctor)
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 4);
    }
}
