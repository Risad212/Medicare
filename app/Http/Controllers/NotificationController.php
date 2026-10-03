<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * In-app notification inbox for any authenticated user.
     * Backend staff (admin + doctor) get the backend view; patients get the
     * Bootstrap-styled frontend view.
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $view = auth()->user()->role === 'patient'
            ? 'frontend.notifications.index'
            : 'backend.notifications.index';

        return view($view, compact('notifications'));
    }

    /**
     * Unread count for the header bell (polled by JS every 60s).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === auth()->id(), 403);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark every notification as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
