<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
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

    public function test_user_can_get_their_notifications()
    {
        // Create notifications for the user
        Notification::factory()->count(3)->create(['user_id' => $this->user->id]);
        // Create notification for another user
        Notification::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->withToken($this->token)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_get_latest_notifications()
    {
        // Create notifications for the user
        Notification::factory()->count(10)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)->getJson('/api/notifications/latest?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_get_unread_count()
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

        $response = $this->withToken($this->token)->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 3
                ]
            ]);
    }

    public function test_user_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'is_read',
                    // ... other fields
                ]
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        // Create unread notifications
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)->postJson('/api/notifications/read-all');

        $response->assertStatus(200);

        // All notifications should be marked as read
        $this->assertEquals(0, Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count());
    }

    public function test_user_can_delete_their_notification()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    public function test_user_cannot_access_other_users_notifications()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(404);
    }

    public function test_user_cannot_mark_other_users_notifications_as_read()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_user_cannot_delete_other_users_notifications()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(404);
    }

    public function test_notifications_are_ordered_by_created_at_desc()
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

        $response = $this->withToken($this->token)->getJson('/api/notifications');

        $data = $response->json('data');
        $this->assertEquals($newestNotification->id, $data[0]['id']);
        $this->assertEquals($newNotification->id, $data[1]['id']);
        $this->assertEquals($oldNotification->id, $data[2]['id']);
    }

    public function test_notification_response_includes_helper_data()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH
        ]);

        $response = $this->withToken($this->token)->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.type_label', 'تذكير بموعد')
            ->assertJsonPath('data.type_icon', 'calendar')
            ->assertJsonPath('data.type_color', '#3B82F6')
            ->assertJsonPath('data.priority_label', 'عالي')
            ->assertJsonPath('data.priority_color', '#EF4444');
    }

    public function test_unauthenticated_user_cannot_access_notifications()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }

    public function test_mark_as_read_returns_updated_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'title' => 'Test Notification'
        ]);

        $response = $this->withToken($this->token)->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.is_read', true)
            ->assertJsonPath('data.title', 'Test Notification');
    }

    public function test_mark_all_as_read_returns_success_message()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->withToken($this->token)->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'data' => null,
                'message' => 'Marked 3 notifications as read'
            ]);
    }

    public function test_latest_notifications_limit_parameter()
    {
        Notification::factory()->count(10)->create(['user_id' => $this->user->id]);

        // Test default limit
        $response = $this->withToken($this->token)->getJson('/api/notifications/latest');
        $response->assertJsonCount(10, 'data');

        // Test custom limit
        $response = $this->withToken($this->token)->getJson('/api/notifications/latest?limit=3');
        $response->assertJsonCount(3, 'data');
    }
}
