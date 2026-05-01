<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get user notifications
     *
     * @param Request $request - Authenticated request
     * @return \Illuminate\Http\JsonResponse - List of user notifications
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->successResponse($notifications);
    }

    /**
     * Create a new notification
     *
     * @param StoreNotificationRequest $request - Validated notification data
     * @return \Illuminate\Http\JsonResponse - Created notification
     */
    public function store(StoreNotificationRequest $request)
    {
        $notification = Notification::create($request->only([
            'user_id',
            'title',
            'message',
            'type',
            'link',
            'data',
        ]));
        return $this->createdResponse($notification);
    }

    /**
     * Get specific notification
     *
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Notification details
     */
    public function show(string $id)
    {
        $notification = Notification::with('user')->findOrFail($id);
        return $this->successResponse($notification);
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
        $notification = Notification::findOrFail($id);
        $notification->update($request->only('is_read'));
        return $this->successResponse($notification);
    }

    /**
     * Delete a notification
     *
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return $this->successResponse(null, 'Notification deleted successfully');
    }
}
