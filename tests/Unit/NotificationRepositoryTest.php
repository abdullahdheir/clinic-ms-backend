<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Notification;
use App\Repositories\NotificationRepository;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new NotificationRepository(new Notification());
        $this->user = User::factory()->create();
    }

    public function test_get_by_user_id_returns_notifications_in_descending_order()
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

        $notifications = $this->repository->getByUserId($this->user->id);

        $this->assertCount(3, $notifications);
        $this->assertEquals($newestNotification->id, $notifications->first()->id);
        $this->assertEquals($oldNotification->id, $notifications->last()->id);
    }

    public function test_get_by_user_id_only_returns_user_notifications()
    {
        $userNotification = Notification::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();
        $otherNotification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $notifications = $this->repository->getByUserId($this->user->id);

        $this->assertCount(1, $notifications);
        $this->assertEquals($userNotification->id, $notifications->first()->id);
    }

    public function test_find_with_relations_includes_user_relationship()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $foundNotification = $this->repository->findWithRelations($notification->id);

        $this->assertNotNull($foundNotification);
        $this->assertTrue($foundNotification->relationLoaded('user'));
        $this->assertEquals($this->user->id, $foundNotification->user->id);
    }

    public function test_find_with_relations_returns_null_for_nonexistent_notification()
    {
        $result = $this->repository->findWithRelations(999);

        $this->assertNull($result);
    }

    public function test_find_with_relations_or_fails_throws_exception_for_nonexistent_notification()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findWithRelationsOrFail(999);
    }

    public function test_find_user_notification_or_fails_returns_user_notification()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $foundNotification = $this->repository->findUserNotificationOrFail($notification->id, $this->user->id);

        $this->assertEquals($notification->id, $foundNotification->id);
        $this->assertEquals($this->user->id, $foundNotification->user_id);
    }

    public function test_find_user_notification_or_fails_throws_exception_for_other_user_notification()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findUserNotificationOrFail($notification->id, $this->user->id);
    }

    public function test_mark_all_as_read_for_user_updates_only_unread_notifications()
    {
        // Create mix of read and unread notifications
        $unreadNotification1 = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        $unreadNotification2 = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        $readNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $count = $this->repository->markAllAsReadForUser($this->user->id);

        $this->assertEquals(2, $count);
        
        // Verify only unread notifications were updated
        $this->assertDatabaseHas('notifications', [
            'id' => $unreadNotification1->id,
            'is_read' => true
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $unreadNotification2->id,
            'is_read' => true
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $readNotification->id,
            'is_read' => true
        ]);
    }

    public function test_mark_all_as_read_for_user_returns_zero_when_no_unread_notifications()
    {
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $count = $this->repository->markAllAsReadForUser($this->user->id);

        $this->assertEquals(0, $count);
    }

    public function test_get_unread_count_for_user()
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

        $count = $this->repository->getUnreadCountForUser($this->user->id);

        $this->assertEquals(3, $count);
    }

    public function test_get_unread_count_for_user_returns_zero_when_no_unread_notifications()
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $count = $this->repository->getUnreadCountForUser($this->user->id);

        $this->assertEquals(0, $count);
    }

    public function test_get_latest_for_user_returns_ordered_notifications_with_limit()
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

        $notifications = $this->repository->getLatestForUser($this->user->id, 2);

        $this->assertCount(2, $notifications);
        $this->assertEquals($newestNotification->id, $notifications->first()->id);
        $this->assertEquals($newNotification->id, $notifications->last()->id);
    }

    public function test_get_latest_for_user_uses_default_limit()
    {
        Notification::factory()->count(15)->create(['user_id' => $this->user->id]);

        $notifications = $this->repository->getLatestForUser($this->user->id);

        $this->assertCount(10, $notifications); // Default limit
    }

    public function test_get_latest_for_user_only_returns_user_notifications()
    {
        $userNotification = Notification::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();
        $otherNotification = Notification::factory()->create(['user_id' => $otherUser->id]);

        $notifications = $this->repository->getLatestForUser($this->user->id, 5);

        $this->assertCount(1, $notifications);
        $this->assertEquals($userNotification->id, $notifications->first()->id);
    }

    public function test_repository_can_create_notification()
    {
        $data = [
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
            'link' => '/appointments/123',
            'data' => ['appointment_id' => 123]
        ];

        $notification = $this->repository->create($data);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertDatabaseHas('notifications', $data);
    }

    public function test_repository_can_update_notification()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $updatedNotification = $this->repository->update($notification->id, [
            'is_read' => true,
            'title' => 'Updated Title'
        ]);

        $this->assertEquals(true, $updatedNotification->is_read);
        $this->assertEquals('Updated Title', $updatedNotification->title);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
            'title' => 'Updated Title'
        ]);
    }

    public function test_repository_can_delete_notification()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $this->repository->delete($notification->id);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }
}
