<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', __('messages.all_notifications_read'));
    }

    public function poll(Request $request)
    {
        $sinceId = (int) $request->get('since_id', 0);

        $notifications = $request->user()->notifications()
            ->where('id', '>', $sinceId)
            ->whereNull('read_at')
            ->orderBy('id')
            ->limit(20)
            ->get(['id', 'title', 'message', 'link']);

        $unreadCount = $request->user()->unreadNotificationsCount();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
