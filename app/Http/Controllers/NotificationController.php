<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Repositories\NotificationRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NotificationRepository $repository
    ) {}

    /**
     * Get user notifications
     *
     * @param Request $request - Authenticated request
     * @return \Illuminate\Http\JsonResponse - List of user notifications
     */
    public function index(Request $request)
    {
        $notifications = $this->repository->getByUserId($request->user()->id);
        return $this->successResponse(NotificationResource::collection($notifications));
    }

    /**
     * Create a new notification
     *
     * @param StoreNotificationRequest $request - Validated notification data
     * @return \Illuminate\Http\JsonResponse - Created notification
     */
    public function store(StoreNotificationRequest $request)
    {
        $notification = $this->repository->create($request->only([
            'user_id',
            'title',
            'message',
            'type',
            'link',
            'data',
        ]));
        return $this->createdResponse(new NotificationResource($notification));
    }

    /**
     * Get specific notification
     *
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Notification details
     */
    public function show(string $id)
    {
        $notification = $this->repository->findUserNotificationOrFail($id, request()->user()->id);
        return $this->successResponse(new NotificationResource($notification));
    }

    /**
     * Mark notification as read
     *
     * @param UpdateNotificationRequest $request - Validated update data
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Updated notification
     */
    public function update(UpdateNotificationRequest $request, string $id)
    {
        $notification = $this->repository->findUserNotificationOrFail($id, request()->user()->id);
        $notification->update($request->only('is_read'));
        return $this->successResponse(new NotificationResource($notification));
    }

    /**
     * Delete a notification
     *
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $notification = $this->repository->findUserNotificationOrFail($id, request()->user()->id);
        $this->repository->delete($id);
        return $this->successResponse(null, 'Notification deleted successfully');
    }

    /**
     * Mark notification as read
     *
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Updated notification
     */
    public function markAsRead(string $id)
    {
        $notification = $this->repository->findUserNotificationOrFail($id, request()->user()->id);
        $notification->update(['is_read' => true]);
        return $this->successResponse(new NotificationResource($notification));
    }

    /**
     * Mark all notifications as read for current user
     *
     * @return \Illuminate\Http\JsonResponse - Success message with count
     */
    public function markAllAsRead()
    {
        $count = $this->repository->markAllAsReadForUser(request()->user()->id);
        return $this->successResponse(null, "Marked {$count} notifications as read");
    }

    /**
     * Get unread notifications count for current user
     *
     * @return \Illuminate\Http\JsonResponse - Unread count
     */
    public function unreadCount()
    {
        $count = $this->repository->getUnreadCountForUser(request()->user()->id);
        return $this->successResponse(['count' => $count]);
    }

    /**
     * Get latest notifications for current user
     *
     * @return \Illuminate\Http\JsonResponse - Latest notifications
     */
    public function latest()
    {
        $notifications = $this->repository->getLatestForUser(request()->user()->id, 10);
        return $this->successResponse(NotificationResource::collection($notifications));
    }
}
