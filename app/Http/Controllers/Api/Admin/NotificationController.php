<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('admin_id', Auth::id())
            ->latest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $notifications,
            'unread'  => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function markRead($id)
    {
        $notification = Notification::where('admin_id', Auth::id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca',
        ]);
    }

    public function markAllRead()
    {
        Notification::where('admin_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca',
        ]);
    }
}