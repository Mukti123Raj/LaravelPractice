<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(10);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();
        
        return response()->json(['success' => true]);
    }
}