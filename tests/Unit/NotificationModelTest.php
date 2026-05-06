<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created_with_enum_types()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('Test Notification', $notification->title);
        $this->assertEquals(NotificationType::APPOINTMENT_REMINDER, $notification->type);
        $this->assertEquals(NotificationPriority::HIGH, $notification->priority);
        $this->assertFalse($notification->is_read);
    }

    public function test_notification_casts_to_enums()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Test',
            'type' => 'appointment_reminder',
            'priority' => 'high',
        ]);

        $this->assertInstanceOf(NotificationType::class, $notification->type);
        $this->assertInstanceOf(NotificationPriority::class, $notification->priority);
        $this->assertEquals(NotificationType::APPOINTMENT_REMINDER, $notification->type);
        $this->assertEquals(NotificationPriority::HIGH, $notification->priority);
    }

    public function test_notification_has_default_priority()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Test',
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        $this->assertEquals(NotificationPriority::MEDIUM, $notification->priority);
    }

    public function test_notification_has_user_relationship()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Test',
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
    }

    public function test_notification_helper_methods()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Test',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);

        // Test type helper methods
        $this->assertEquals('تذكير بموعد', $notification->getTypeLabel());
        $this->assertEquals('calendar', $notification->getTypeIcon());
        $this->assertEquals('#3B82F6', $notification->getTypeColor());

        // Test priority helper methods
        $this->assertEquals('عالي', $notification->getPriorityLabel());
        $this->assertEquals('#EF4444', $notification->getPriorityColor());
    }

    public function test_notification_scopes()
    {
        $user = User::factory()->create();
        
        // Create notifications with different statuses
        $unreadNotification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Unread',
            'message' => 'Unread',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'is_read' => false,
        ]);

        $readNotification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Read',
            'message' => 'Read',
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'is_read' => true,
        ]);

        $highPriorityNotification = Notification::create([
            'user_id' => $user->id,
            'title' => 'High Priority',
            'message' => 'High Priority',
            'type' => NotificationType::APPOINTMENT_CANCELLED,
            'priority' => NotificationPriority::HIGH,
        ]);

        // Test unread scope
        $unreadNotifications = Notification::unread()->get();
        $this->assertCount(2, $unreadNotifications);
        $this->assertTrue($unreadNotifications->contains($unreadNotification));
        $this->assertFalse($unreadNotifications->contains($readNotification));

        // Test priority scope
        $highPriorityNotifications = Notification::priority(NotificationPriority::HIGH)->get();
        $this->assertCount(1, $highPriorityNotifications);
        $this->assertTrue($highPriorityNotifications->contains($highPriorityNotification));

        // Test type scope
        $reminderNotifications = Notification::type(NotificationType::APPOINTMENT_REMINDER)->get();
        $this->assertCount(1, $reminderNotifications);
        $this->assertTrue($reminderNotifications->contains($unreadNotification));

        // Test user scope
        $userNotifications = Notification::forUser($user->id)->get();
        $this->assertCount(3, $userNotifications);

        // Test latest scope
        $latestNotifications = Notification::latest()->take(2)->get();
        $this->assertCount(2, $latestNotifications);
        $this->assertEquals($highPriorityNotification->id, $latestNotifications->first()->id);
    }

    public function test_notification_data_attribute_is_json()
    {
        $user = User::factory()->create();
        $testData = ['appointment_id' => 123, 'doctor_name' => 'Dr. Test'];
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Test',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'data' => $testData,
        ]);

        $this->assertIsArray($notification->data);
        $this->assertEquals($testData, $notification->data);
    }
}
