<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiNotificationController extends ApiController
{
    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('unread_only') && $request->unread_only) {
            $query->whereNull('read_at');
        }

        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            NotificationResource::collection($notifications),
            $this->getPaginationMeta($notifications),
            'Lấy danh sách thông báo thành công'
        );
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        $count = Auth::user()->unreadNotifications()->count();

        return $this->successResponse([
            'unread_count' => $count,
        ], 'Lấy số thông báo chưa đọc thành công');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);

            if ($notification->unread()) {
                $notification->markAsRead();
            }

            return $this->successResponse(
                new NotificationResource($notification),
                'Đánh dấu đã đọc thành công'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();

            return $this->successResponse(null, 'Đã đánh dấu tất cả thông báo là đã đọc');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->delete();

            return $this->successResponse(null, 'Xóa thông báo thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
