<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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
        return response()->json($notifications);
    }

    /**
     * Create a new notification
     *
     * @param Request $request - Notification data (user_id, title, message, type)
     * @return \Illuminate\Http\JsonResponse - Created notification
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'in:info,success,warning,error',
            'link' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notification = Notification::create($request->all());
        return response()->json($notification, 201);
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
        return response()->json($notification);
    }

    /**
     * Mark notification as read
     *
     * @param Request $request - Update data
     * @param string $id - Notification ID
     * @return \Illuminate\Http\JsonResponse - Updated notification
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        $request->validate([
            'is_read' => 'boolean',
        ]);

        $notification->update($request->only('is_read'));
        return response()->json($notification);
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
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
