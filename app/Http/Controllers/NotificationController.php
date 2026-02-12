<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý thông báo cho người dùng
 */
class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Danh sách thông báo của user
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy database notifications
        $notifications = \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', \App\Models\User::class)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $unreadCount = $this->notificationService->countDatabaseUnread($user);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Đánh dấu một thông báo đã đọc
     */
    public function markAsRead(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notification = \App\Models\Notification::where('id', $id)
            ->where('notifiable_id', $user->id)
            ->first();
        
        if ($notification) {
            $notification->markAsRead();
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $this->notificationService->countDatabaseUnread($user),
            ]);
        }

        return back()->with('success', 'Đã đánh dấu là đã đọc.');
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', \App\Models\User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
    }

    /**
     * Lấy số thông báo chưa đọc (AJAX)
     */
    public function getUnreadCount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        return response()->json([
            'count' => $this->notificationService->countDatabaseUnread($user),
        ]);
    }

    /**
     * Lấy danh sách thông báo mới nhất (AJAX cho dropdown)
     */
    public function getLatest()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = $this->notificationService->getDatabaseNotifications($user, 10);
        
        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                $data = $notification->data;
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Thông báo',
                    'message' => $data['message'] ?? '',
                    'icon' => $data['icon'] ?? 'bi-bell',
                    'color' => $data['color'] ?? 'primary',
                    'url' => $data['url'] ?? '#',
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $this->notificationService->countDatabaseUnread($user),
        ]);
    }
    
    /**
     * Xóa thông báo
     */
    public function destroy(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notification = \App\Models\Notification::where('id', $id)
            ->where('notifiable_id', $user->id)
            ->first();
        
        if ($notification) {
            $notification->delete();
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $this->notificationService->countDatabaseUnread($user),
            ]);
        }

        return back()->with('success', 'Đã xóa thông báo');
    }
    
    /**
     * Xóa tất cả thông báo đã đọc
     */
    public function destroyRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', \App\Models\User::class)
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', 'Đã xóa tất cả thông báo đã đọc');
    }
}
