<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository extends BaseRepository
{
    /**
     * NotificationRepository constructor
     *
     * @param Notification $model
     */
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get notifications by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find notification with relationships
     *
     * @param int|string $id
     * @return Notification|null
     */
    public function findWithRelations(int|string $id): ?Notification
    {
        return $this->model->with('user')->find($id);
    }

    /**
     * Find notification with relationships or throw exception
     *
     * @param int|string $id
     * @return Notification
     */
    public function findWithRelationsOrFail(int|string $id): Notification
    {
        return $this->model->with('user')->findOrFail($id);
    }

    /**
     * Find user notification or throw exception
     *
     * @param int|string $id
     * @param int $userId
     * @return Notification
     */
    public function findUserNotificationOrFail(int|string $id, int $userId): Notification
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    /**
     * Mark all notifications as read for user
     *
     * @param int $userId
     * @return int Number of updated notifications
     */
    public function markAllAsReadForUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get unread notifications count for user
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get latest notifications for user
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getLatestForUser(int $userId, int $limit = 10): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
