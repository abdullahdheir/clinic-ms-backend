<?php

namespace Tests\Unit;

use App\Jobs\AppointmentReminderJob;
use App\Jobs\SendNotificationEmailJob;
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
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Carbon\Carbon;
use Exception;

class NotificationJobTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;
    private Doctor $doctor;
    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->clinic = Clinic::factory()->create();
        $this->doctor = Doctor::factory()->create();
        $this->patient = User::factory()->create();
    }

    public function test_appointment_reminder_job_handles_exceptions_gracefully()
    {
        // Create appointment that will cause an error
        $appointment = Appointment::create([
            'patient_id' => 999, // Non-existent patient
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock logging to capture error
        Log::shouldReceive('error')->once();

        // Run job and ensure it doesn't crash
        $job = new AppointmentReminderJob();
        
        // Should handle the exception gracefully
        expect(fn() => $job->handle())->not->toThrow(Exception::class);
    }

    public function test_appointment_reminder_job_logs_processing_info()
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock logging to verify info messages
        Log::shouldReceive('info')->atLeast()->once();

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Verify job completed successfully
        $this->assertTrue(true);
    }

    public function test_appointment_reminder_job_handles_missing_user_relationships()
    {
        // Create appointment with non-existent patient
        $appointment = Appointment::create([
            'patient_id' => 999, // Non-existent patient
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock logging for missing user
        Log::shouldReceive('warning')->once();

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Should still create notification for doctor
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->doctor->user_id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        // Should not create notification for patient (no user)
        $this->assertDatabaseMissing('notifications', [
            'user_id' => null,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);
    }

    public function test_appointment_reminder_job_respects_appointment_time_windows()
    {
        // Test exact 2-hour window
        $exact2HourAppointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2)->addMinutes(30),
            'status' => 'confirmed',
        ]);

        // Test just outside 2-hour window
        $outside2HourAppointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2)->addMinutes(31),
            'status' => 'confirmed',
        ]);

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Should process exact 2-hour appointment
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->patient->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        // Should not process appointment outside window
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->patient->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
            'data' => json_encode(['appointment_id' => $outside2HourAppointment->id]),
        ]);
    }

    public function test_appointment_reminder_job_handles_doctor_without_user()
    {
        // Create doctor without user
        $doctorWithoutUser = Doctor::factory()->create(['user_id' => null]);
        
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctorWithoutUser->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Mock logging for missing user
        Log::shouldReceive('warning')->once();

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Should still create notification for patient
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->patient->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        // Should not create notification for doctor (no user)
        $this->assertDatabaseMissing('notifications', [
            'user_id' => null,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);
    }

    public function test_appointment_reminder_job_creates_notifications_with_correct_links()
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Verify notification links
        $patientNotification = Notification::where('user_id', $this->patient->id)->first();
        $doctorNotification = Notification::where('user_id', $this->doctor->user_id)->first();

        $this->assertEquals('/appointments/' . $appointment->id, $patientNotification->link);
        $this->assertEquals('/appointments/' . $appointment->id, $doctorNotification->link);

        // Verify notification data
        $this->assertEquals(['appointment_id' => $appointment->id], $patientNotification->data);
        $this->assertEquals(['appointment_id' => $appointment->id], $doctorNotification->data);
    }

    public function test_appointment_reminder_job_handles_multiple_reminders_for_same_appointment()
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(24),
            'status' => 'confirmed',
        ]);

        Mail::fake();

        // Run job for 24-hour reminder
        $job1 = new AppointmentReminderJob();
        $job1->handle();

        // Check 24-hour notifications created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->patient->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        // Update appointment time to 2 hours from now
        $appointment->update(['scheduled_at' => Carbon::now()->addHours(2)]);

        // Run job for 2-hour reminder
        $job2 = new AppointmentReminderJob();
        $job2->handle();

        // Check 2-hour notifications created
        $notifications = Notification::where('user_id', $this->patient->id)
            ->where('type', NotificationType::APPOINTMENT_REMINDER)
            ->get();

        // Should have both MEDIUM (24h) and HIGH (2h) priority notifications
        $this->assertTrue($notifications->contains('priority', NotificationPriority::MEDIUM));
        $this->assertTrue($notifications->contains('priority', NotificationPriority::HIGH));
    }

    public function test_send_notification_email_job_handles_missing_notification()
    {
        // Mock logging for error
        Log::shouldReceive('error')->once();

        $job = new SendNotificationEmailJob(999);
        
        // Should handle missing notification gracefully
        expect(fn() => $job->handle())->not->toThrow(Exception::class);
    }

    public function test_send_notification_email_job_handles_missing_user()
    {
        $notification = Notification::factory()->create(['user_id' => 999]);

        // Mock logging for error
        Log::shouldReceive('error')->once();

        $job = new SendNotificationEmailJob($notification->id);
        
        // Should handle missing user gracefully
        expect(fn() => $job->handle())->not->toThrow(Exception::class);
    }

    public function test_send_notification_email_job_handles_email_sending_failure()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        // Mock Mail to throw exception
        Mail::fake();
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new Exception('SMTP server unavailable'));

        // Mock logging for error
        Log::shouldReceive('error')->once();

        $job = new SendNotificationEmailJob($notification->id);
        
        // Should handle email failure gracefully
        expect(fn() => $job->handle())->not->toThrow(Exception::class);
    }

    public function test_appointment_reminder_job_performance_with_many_appointments()
    {
        // Create many appointments
        $appointments = Appointment::factory()->count(100)->create([
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Create patients for these appointments
        foreach ($appointments as $appointment) {
            $patient = User::factory()->create();
            $appointment->update(['patient_id' => $patient->id]);
        }

        Mail::fake();

        $startTime = microtime(true);
        
        $job = new AppointmentReminderJob();
        $job->handle();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (5 seconds for 100 appointments)
        $this->assertLessThan(5.0, $executionTime);

        // Should create notifications for all appointments
        $notificationCount = Notification::where('type', NotificationType::APPOINTMENT_REMINDER)->count();
        $this->assertEquals(200, $notificationCount); // 100 patients + 100 doctors
    }

    public function test_appointment_reminder_job_batch_processing()
    {
        // Create appointments that need processing
        $appointments = collect();
        for ($i = 0; $i < 50; $i++) {
            $patient = User::factory()->create();
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'clinic_id' => $this->clinic->id,
                'scheduled_at' => Carbon::now()->addHours(2),
                'status' => 'confirmed',
            ]);
            $appointments->push($appointment);
        }

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Verify all appointments were processed
        foreach ($appointments as $appointment) {
            $this->assertDatabaseHas('notifications', [
                'type' => NotificationType::APPOINTMENT_REMINDER,
                'data' => json_encode(['appointment_id' => $appointment->id]),
            ]);
        }

        // Verify batch email sending
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 100); // 50 patients + 50 doctors
    }

    public function test_appointment_reminder_job_idempotency()
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        Mail::fake();

        // Run job multiple times
        $job = new AppointmentReminderJob();
        $job->handle();
        $job->handle();
        $job->handle();

        // Should create notifications each time (not idempotent by design)
        $notificationCount = Notification::where('user_id', $this->patient->id)
            ->where('type', NotificationType::APPOINTMENT_REMINDER)
            ->count();

        $this->assertEquals(3, $notificationCount);

        // Should queue emails each time
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 6); // 3 runs * 2 emails
    }

    public function test_appointment_reminder_job_with_different_appointment_statuses()
    {
        $statuses = ['confirmed', 'pending', 'in_progress', 'completed', 'cancelled'];
        
        foreach ($statuses as $status) {
            $patient = User::factory()->create();
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'clinic_id' => $this->clinic->id,
                'scheduled_at' => Carbon::now()->addHours(2),
                'status' => $status,
            ]);
        }

        Mail::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // Should only process confirmed appointments
        foreach ($statuses as $status) {
            $expectedCount = ($status === 'confirmed') ? 1 : 0;
            
            $this->assertEquals(
                $expectedCount,
                Notification::whereHas('user.patient.appointments', function ($query) use ($status) {
                    $query->where('status', $status);
                })->where('type', NotificationType::APPOINTMENT_REMINDER)->count(),
                "Status '{$status}' should have {$expectedCount} notifications"
            );
        }
    }
}
