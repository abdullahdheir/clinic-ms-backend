<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use App\Jobs\AppointmentReminderJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $patientUser;
    private User $doctorUser;
    private Doctor $doctor;
    private Clinic $clinic;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test data
        $this->clinic = Clinic::factory()->create();
        $this->doctor = Doctor::factory()->create();
        
        $this->patientUser = User::factory()->create();
        $this->doctorUser = User::factory()->create();
        $this->doctor->user_id = $this->doctorUser->id;
        $this->doctor->save();
    }

    public function test_complete_appointment_notification_workflow()
    {
        // 1. Create appointment
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // 2. Run reminder job
        Mail::fake();
        Queue::fake();

        $job = new AppointmentReminderJob();
        $job->handle();

        // 3. Verify notifications created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->patientUser->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->doctorUser->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        // 4. Verify emails queued
        Mail::assertQueued(\App\Mail\AppointmentReminderMail::class, 2);

        // 5. Test API workflow - patient fetches notifications
        $patientToken = $this->patientUser->createToken('test-token')->plainTextToken;
        $response = $this->withToken($patientToken)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', NotificationType::APPOINTMENT_REMINDER)
            ->assertJsonPath('data.0.priority', NotificationPriority::HIGH);

        // 6. Test marking as read
        $notification = Notification::where('user_id', $this->patientUser->id)->first();
        $response = $this->withToken($patientToken)->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_read', true);

        // 7. Verify unread count updated
        $response = $this->withToken($patientToken)->getJson('/api/notifications/unread-count');
        $response->assertJson(['data' => ['count' => 0]]);
    }

    public function test_appointment_cancellation_notification_workflow()
    {
        // 1. Create and confirm appointment
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(24),
            'status' => 'confirmed',
        ]);

        // 2. Cancel appointment (this should trigger cancellation notifications)
        $appointment->update(['status' => 'cancelled']);

        // 3. Manually create cancellation notifications (in real app, this would be triggered by event)
        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'إلغاء الموعد',
            'message' => 'تم إلغاء موعدك بنجاح',
            'type' => NotificationType::APPOINTMENT_CANCELLED,
            'priority' => NotificationPriority::MEDIUM,
            'link' => '/appointments/' . $appointment->id,
            'data' => ['appointment_id' => $appointment->id],
        ]);

        Notification::create([
            'user_id' => $this->doctorUser->id,
            'title' => 'إلغاء موعد',
            'message' => "تم إلغاء موعد المريض {$this->patientUser->name}",
            'type' => NotificationType::APPOINTMENT_CANCELLED,
            'priority' => NotificationPriority::MEDIUM,
            'link' => '/appointments/' . $appointment->id,
            'data' => ['appointment_id' => $appointment->id],
        ]);

        // 4. Verify both users receive notifications
        $patientToken = $this->patientUser->createToken('test-token')->plainTextToken;
        $doctorToken = $this->doctorUser->createToken('test-token')->plainTextToken;

        $patientResponse = $this->withToken($patientToken)->getJson('/api/notifications');
        $doctorResponse = $this->withToken($doctorToken)->getJson('/api/notifications');

        $patientResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', NotificationType::APPOINTMENT_CANCELLED);

        $doctorResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', NotificationType::APPOINTMENT_CANCELLED);
    }

    public function test_multiple_users_notification_isolation()
    {
        // Create another patient and doctor
        $otherPatientUser = User::factory()->create();

        // Create appointment for original patient
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Create notifications for original patient
        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'موعد جديد',
            'message' => 'لديك موعد جديد',
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        // Create notifications for other patient
        Notification::create([
            'user_id' => $otherPatientUser->id,
            'title' => 'موعد آخر',
            'message' => 'هذا موعد لمستخدم آخر',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        // Verify notification isolation
        $patientToken = $this->patientUser->createToken('test-token')->plainTextToken;
        $otherPatientToken = $otherPatientUser->createToken('test-token')->plainTextToken;

        $patientResponse = $this->withToken($patientToken)->getJson('/api/notifications');
        $otherPatientResponse = $this->withToken($otherPatientToken)->getJson('/api/notifications');

        $patientResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'موعد جديد');

        $otherPatientResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'موعد آخر');
    }

    public function test_notification_priority_workflow()
    {
        // Create notifications with different priorities
        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'تذكير مهم',
            'message' => 'موعدك خلال ساعتين',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::URGENT,
        ]);

        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'تأكيد موعد',
            'message' => 'تم تأكيد موعدك',
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'priority' => NotificationPriority::LOW,
        ]);

        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'فاتورة جديدة',
            'message' => 'لديك فاتورة جديدة',
            'type' => NotificationType::NEW_INVOICE,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        $token = $this->patientUser->createToken('test-token')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Verify priority colors and labels are included
        $notifications = $response->json('data');
        
        $urgentNotification = collect($notifications)->firstWhere('priority', NotificationPriority::URGENT);
        $lowNotification = collect($notifications)->firstWhere('priority', NotificationPriority::LOW);
        $mediumNotification = collect($notifications)->firstWhere('priority', NotificationPriority::MEDIUM);

        $this->assertEquals('#DC2626', $urgentNotification['priority_color']);
        $this->assertEquals('عاجل', $urgentNotification['priority_label']);
        
        $this->assertEquals('#6B7280', $lowNotification['priority_color']);
        $this->assertEquals('منخفض', $lowNotification['priority_label']);
        
        $this->assertEquals('#F59E0B', $mediumNotification['priority_color']);
        $this->assertEquals('متوسط', $mediumNotification['priority_label']);
    }

    public function test_notification_type_workflow()
    {
        // Create notifications of different types
        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'موعد جديد',
            'message' => 'تم حجز موعد جديد',
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'priority' => NotificationPriority::MEDIUM,
        ]);

        Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'زيارة مكتملة',
            'message' => 'تم إنهاء زيارتك بنجاح',
            'type' => NotificationType::VISIT_COMPLETED,
            'priority' => NotificationPriority::LOW,
        ]);

        $token = $this->patientUser->createToken('test-token')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $notifications = $response->json('data');
        
        $appointmentNotification = collect($notifications)->firstWhere('type', NotificationType::APPOINTMENT_CONFIRMED);
        $visitNotification = collect($notifications)->firstWhere('type', NotificationType::VISIT_COMPLETED);

        // Verify type-specific data
        $this->assertEquals('تأكيد الموعد', $appointmentNotification['type_label']);
        $this->assertEquals('check-circle', $appointmentNotification['type_icon']);
        $this->assertEquals('#10B981', $appointmentNotification['type_color']);
        
        $this->assertEquals('انتهاء الزيارة', $visitNotification['type_label']);
        $this->assertEquals('user-check', $visitNotification['type_icon']);
        $this->assertEquals('#8B5CF6', $visitNotification['type_color']);
    }

    public function test_bulk_notification_operations_workflow()
    {
        // Create multiple unread notifications
        Notification::factory()->count(5)->create([
            'user_id' => $this->patientUser->id,
            'is_read' => false,
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        $token = $this->patientUser->createToken('test-token')->plainTextToken;

        // 1. Check initial unread count
        $response = $this->withToken($token)->getJson('/api/notifications/unread-count');
        $response->assertJsonPath('data.count', 5);

        // 2. Mark all as read
        $response = $this->withToken($token)->postJson('/api/notifications/read-all');
        $response->assertStatus(200)
            ->assertJson([
                'data' => null,
                'message' => 'Marked 5 notifications as read'
            ]);

        // 3. Verify all are now read
        $response = $this->withToken($token)->getJson('/api/notifications/unread-count');
        $response->assertJson(['data' => ['count' => 0]]);

        // 4. Delete notifications one by one
        $notifications = Notification::where('user_id', $this->patientUser->id)->get();
        
        foreach ($notifications as $notification) {
            $response = $this->withToken($token)->deleteJson("/api/notifications/{$notification->id}");
            $response->assertStatus(200);
        }

        // 5. Verify all notifications are deleted
        $response = $this->withToken($token)->getJson('/api/notifications');
        $response->assertJsonCount(0, 'data');
    }

    public function test_notification_with_linked_resources_workflow()
    {
        // Create appointment
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'confirmed',
        ]);

        // Create notification with link and data
        $notification = Notification::create([
            'user_id' => $this->patientUser->id,
            'title' => 'موعد جديد',
            'message' => 'لديك موعد جديد مع د. ' . $this->doctor->user->name,
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'priority' => NotificationPriority::MEDIUM,
            'link' => '/appointments/' . $appointment->id,
            'data' => [
                'appointment_id' => $appointment->id,
                'doctor_name' => $this->doctor->user->name,
                'clinic_name' => $this->clinic->name,
                'scheduled_at' => $appointment->scheduled_at->toISOString(),
            ],
        ]);

        $token = $this->patientUser->createToken('test-token')->plainTextToken;
        $response = $this->withToken($token)->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.link', '/appointments/' . $appointment->id)
            ->assertJsonPath('data.data.appointment_id', $appointment->id)
            ->assertJsonPath('data.data.doctor_name', $this->doctor->user->name)
            ->assertJsonPath('data.data.clinic_name', $this->clinic->name);
    }

    public function test_notification_error_handling_workflow()
    {
        $token = $this->patientUser->createToken('test-token')->plainTextToken;

        // Test accessing non-existent notification
        $response = $this->withToken($token)->getJson('/api/notifications/999');
        $response->assertStatus(404);

        // Test marking non-existent notification as read
        $response = $this->withToken($token)->postJson('/api/notifications/999/read');
        $response->assertStatus(404);

        // Test deleting non-existent notification
        $response = $this->withToken($token)->deleteJson('/api/notifications/999');
        $response->assertStatus(404);

        // Test unauthorized access to other user's notification
        $otherUser = User::factory()->create();
        $otherNotification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($token)->getJson("/api/notifications/{$otherNotification->id}");
        $response->assertStatus(404);

        $response = $this->withToken($token)->postJson("/api/notifications/{$otherNotification->id}/read");
        $response->assertStatus(404);

        $response = $this->withToken($token)->deleteJson("/api/notifications/{$otherNotification->id}");
        $response->assertStatus(404);
    }
}
