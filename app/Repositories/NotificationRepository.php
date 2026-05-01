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
}
