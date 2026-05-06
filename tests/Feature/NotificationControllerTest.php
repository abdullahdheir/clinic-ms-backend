<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_store_creates_notification_with_valid_data()
    {
        $notificationData = [
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
            'link' => '/appointments/123',
            'data' => ['appointment_id' => 123]
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/notifications', $notificationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'title',
                    'message',
                    'type',
                    'priority',
                    'is_read',
                    'link',
                    'data',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'type' => NotificationType::APPOINTMENT_REMINDER,
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/notifications', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'title', 'message', 'type']);
    }

    public function test_store_validates_enum_values()
    {
        $invalidData = [
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'type' => 'invalid_type',
            'priority' => 'invalid_priority',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/notifications', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'priority']);
    }

    public function test_show_returns_notification_with_relationships()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'title',
                    'message',
                    'type',
                    'priority',
                    'is_read',
                    'link',
                    'data',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_notification()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_notification_with_valid_data()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'title' => 'Original Title'
        ]);

        $updateData = [
            'is_read' => true,
            'title' => 'Updated Title'
        ];

        $response = $this->withToken($this->token)
            ->putJson("/api/notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_read', true)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
            'title' => 'Updated Title'
        ]);
    }

    public function test_update_validates_allowed_fields()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $invalidUpdateData = [
            'user_id' => 999,
            'type' => 'appointment_confirmed',
            'invalid_field' => 'value'
        ];

        $response = $this->withToken($this->token)
            ->putJson("/api/notifications/{$notification->id}", $invalidUpdateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'type', 'invalid_field']);
    }

    public function test_destroy_deletes_notification()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => null,
                'message' => 'Notification deleted successfully'
            ]);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_notification()
    {
        $response = $this->withToken($this->token)
            ->deleteJson('/api/notifications/999');

        $response->assertStatus(404);
    }

    public function test_mark_as_read_updates_notification_read_status()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    public function test_mark_as_read_returns_404_for_other_user_notification()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_mark_all_as_read_updates_all_user_notifications()
    {
        // Create mix of read and unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'data' => null,
                'message' => 'Marked 3 notifications as read'
            ]);

        // All notifications should now be read
        $this->assertEquals(0, Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count());
    }

    public function test_mark_all_as_read_returns_zero_message_when_no_unread_notifications()
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'data' => null,
                'message' => 'Marked 0 notifications as read'
            ]);
    }

    public function test_unread_count_returns_correct_count()
    {
        // Create mix of read and unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 3
                ]
            ]);
    }

    public function test_unread_count_returns_zero_when_no_unread_notifications()
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 0
                ]
            ]);
    }

    public function test_latest_returns_ordered_notifications_with_default_limit()
    {
        // Create notifications with different timestamps
        $oldNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subHours(2)
        ]);
        $newNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subMinutes(30)
        ]);
        $newestNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/latest');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data'); // Default limit

        $data = $response->json('data');
        $this->assertEquals($newestNotification->id, $data[0]['id']);
        $this->assertEquals($newNotification->id, $data[1]['id']);
        $this->assertEquals($oldNotification->id, $data[2]['id']);
    }

    public function test_latest_respects_limit_parameter()
    {
        Notification::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/latest?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_latest_only_returns_user_notifications()
    {
        $userNotification = Notification::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();
        $otherNotification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/notifications/latest');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $userNotification->id);
    }

    public function test_all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', '/api/notifications'],
            ['POST', '/api/notifications'],
            ['GET', '/api/notifications/1'],
            ['PUT', '/api/notifications/1'],
            ['DELETE', '/api/notifications/1'],
            ['POST', '/api/notifications/1/read'],
            ['POST', '/api/notifications/read-all'],
            ['GET', '/api/notifications/unread-count'],
            ['GET', '/api/notifications/latest'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401);
        }
    }

    public function test_notification_response_includes_helper_data()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.type_label', 'تذكير بموعد')
            ->assertJsonPath('data.type_icon', 'calendar')
            ->assertJsonPath('data.type_color', '#3B82F6')
            ->assertJsonPath('data.priority_label', 'عالي')
            ->assertJsonPath('data.priority_color', '#EF4444');
    }
}
