<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        // For students, notifications are attached to Student model
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;

        $notifications = $notifiable->notifications()->latest()->paginate(10);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifiable->unreadNotifications()->count()
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;
        $count = $notifiable->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;
        $notification = $notifiable->notifications()->find($notificationId);
        
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
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;
        $notifiable->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId)
    {
        $user = Auth::user();
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;
        $notification = $notifiable->notifications()->find($notificationId);
        
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
        $notifiable = $user && $user->role === 'student'
            ? (Student::where('email', $user->email)->first() ?: $user)
            : $user;
        $notifiable->notifications()->delete();
        
        return response()->json(['success' => true]);
    }
}