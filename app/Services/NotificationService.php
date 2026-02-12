<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use App\Notifications\NewBookingNotification;
use App\Notifications\BookingWithCouponNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\NewReviewNotification;
use App\Notifications\SystemAlertNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\RefundProcessingNotification;
use App\Notifications\RefundCompletedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service quản lý gửi thông báo cho đúng đối tượng
 */
class NotificationService
{
    /**
     * Gửi thông báo khi có booking mới
     * -> Gửi cho booking_manager
     */
    public function notifyNewBooking(Booking $booking): void
    {
        try {
            $bookingManagers = $this->getBookingManagers();
            
            if ($bookingManagers->isNotEmpty()) {
                Notification::send($bookingManagers, new NewBookingNotification($booking));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send new booking notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi booking có áp dụng coupon
     * -> Gửi cho super_admin (vì liên quan đến coupon)
     */
    public function notifyBookingWithCoupon(Booking $booking): void
    {
        try {
            $superAdmins = $this->getSuperAdmins();
            
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new BookingWithCouponNotification($booking));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send booking with coupon notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi thanh toán thành công
     * -> Gửi cho user (chủ booking)
     * -> Gửi cho booking_manager (quản lý đơn hàng)
     */
    public function notifyPaymentSuccess(Booking $booking): void
    {
        try {
            // Gửi cho user
            if ($booking->user) {
                $booking->user->notify(new PaymentSuccessNotification($booking));
            }

            // Gửi cho booking_manager (họ quản lý đơn đặt vé)
            $bookingManagers = $this->getBookingManagers();
            if ($bookingManagers->isNotEmpty()) {
                Notification::send($bookingManagers, new PaymentSuccessNotification($booking, true));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment success notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi thanh toán thất bại
     * -> Gửi cho user (chủ booking)
     * -> Gửi cho booking_manager
     * -> Gửi cho super_admin
     */
    public function notifyPaymentFailed(Booking $booking, string $errorCode = '', string $errorMessage = ''): void
    {
        try {
            // Gửi cho user
            if ($booking->user) {
                $booking->user->notify(new PaymentFailedNotification($booking, $errorMessage));
            }

            // Gửi cho booking_manager
            $bookingManagers = $this->getBookingManagers();
            if ($bookingManagers->isNotEmpty()) {
                Notification::send($bookingManagers, new PaymentFailedNotification($booking, $errorMessage, true));
            }

            // Gửi cho super_admin
            $superAdmins = $this->getSuperAdmins();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new PaymentFailedNotification($booking, $errorMessage, false, true));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment failed notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi có review mới
     * -> Gửi cho content_manager (quản lý nội dung)
     */
    public function notifyNewReview(Review $review): void
    {
        try {
            $contentManagers = $this->getContentManagers();
            
            if ($contentManagers->isNotEmpty()) {
                Notification::send($contentManagers, new NewReviewNotification($review));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send new review notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi booking bị hủy
     * -> Nếu admin hủy: gửi cho user
     * -> Nếu user hủy: gửi cho booking_manager
     */
    public function notifyBookingCancelled(Booking $booking, User $cancelledByUser, bool $isAdminCancelled = false): void
    {
        try {
            if ($isAdminCancelled) {
                // Admin hủy -> thông báo cho user
                if ($booking->user) {
                    $booking->user->notify(new BookingCancelledNotification($booking, $cancelledByUser, true));
                }
            } else {
                // User hủy -> thông báo cho booking_manager
                $bookingManagers = $this->getBookingManagers();
                if ($bookingManagers->isNotEmpty()) {
                    Notification::send($bookingManagers, new BookingCancelledNotification($booking, $cancelledByUser, false));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send booking cancelled notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi đang xử lý hoàn tiền
     * -> Gửi cho user (chủ booking)
     * -> Gửi cho booking_manager
     */
    public function notifyRefundProcessing(Booking $booking): void
    {
        try {
            // Gửi cho user
            if ($booking->user) {
                $booking->user->notify(new RefundProcessingNotification($booking));
            }

            // Gửi cho booking_manager
            $bookingManagers = $this->getBookingManagers();
            if ($bookingManagers->isNotEmpty()) {
                Notification::send($bookingManagers, new RefundProcessingNotification($booking));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send refund processing notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo khi hoàn tiền hoàn tất (thành công hoặc thất bại)
     * -> Gửi cho user (chủ booking)
     * -> Gửi cho booking_manager
     * -> Nếu thất bại: gửi cho super_admin
     */
    public function notifyRefundCompleted(Booking $booking, bool $success, string $message = ''): void
    {
        try {
            // Gửi cho user
            if ($booking->user) {
                $booking->user->notify(new RefundCompletedNotification($booking, $success, $message));
            }

            // Gửi cho booking_manager
            $bookingManagers = $this->getBookingManagers();
            if ($bookingManagers->isNotEmpty()) {
                Notification::send($bookingManagers, new RefundCompletedNotification($booking, $success, $message));
            }

            // Nếu thất bại, gửi cho super_admin
            if (!$success) {
                $superAdmins = $this->getSuperAdmins();
                if ($superAdmins->isNotEmpty()) {
                    Notification::send($superAdmins, new RefundCompletedNotification($booking, $success, $message));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send refund completed notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo lỗi hệ thống nghiêm trọng
     * -> Gửi cho super_admin
     */
    public function notifySystemAlert(string $title, string $message, string $level = 'error'): void
    {
        try {
            $superAdmins = $this->getSuperAdmins();
            
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new SystemAlertNotification($title, $message, $level));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send system alert notification: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách super_admin
     */
    protected function getSuperAdmins()
    {
        return User::where('role', 'super_admin')->get();
    }

    /**
     * Lấy danh sách booking_manager
     */
    protected function getBookingManagers()
    {
        return User::where('role', 'booking_manager')->get();
    }

    /**
     * Lấy danh sách content_manager
     */
    protected function getContentManagers()
    {
        return User::where('role', 'content_manager')->get();
    }

    /**
     * Lấy danh sách tất cả admin (cho các thông báo chung)
     */
    protected function getAllAdmins()
    {
        return User::whereIn('role', ['super_admin', 'booking_manager', 'content_manager'])->get();
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc cho user
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Đánh dấu một thông báo đã đọc
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Lấy số thông báo chưa đọc của user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications->count();
    }

    /**
     * Lấy danh sách thông báo của user
     */
    public function getNotifications(User $user, int $limit = 20)
    {
        return $user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
    }

    /**
     * Gửi thông báo database đến user cụ thể
     */
    public function sendDatabaseNotification(User $user, string $type, array $data): void
    {
        \App\Models\Notification::create([
            'type' => $type,
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'data' => $data,
        ]);
    }

    /**
     * Gửi thông báo database đến nhiều users
     */
    public function sendDatabaseNotificationToMany(array $userIds, string $type, array $data): int
    {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $type,
                'notifiable_id' => $userId,
                'notifiable_type' => User::class,
                'data' => json_encode($data),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return \Illuminate\Support\Facades\DB::table('notifications')->insert($notifications);
    }

    /**
     * Thông báo khi có contact message mới
     */
    public function notifyNewContact($contact): void
    {
        $adminIds = User::whereIn('role', ['super_admin', 'booking_manager', 'content_manager'])
            ->pluck('id')
            ->toArray();

        if (!empty($adminIds)) {
            $this->sendDatabaseNotificationToMany(
                $adminIds,
                'new_contact',
                [
                    'title' => 'Liên hệ mới',
                    'message' => "{$contact->name} đã gửi tin nhắn liên hệ",
                    'contact_id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'url' => route('admin.contacts.show', $contact->id),
                    'icon' => 'bi-envelope',
                    'color' => 'info',
                ]
            );
        }
    }

    /**
     * Thông báo khi tour được cập nhật
     */
    public function notifyTourUpdated($tour, array $changes = []): void
    {
        // Lấy users đã wishlist tour này
        $userIds = \Illuminate\Support\Facades\DB::table('wishlists')
            ->where('tour_id', $tour->id)
            ->pluck('user_id')
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        $message = "Tour {$tour->name} đã có cập nhật";
        if (isset($changes['price'])) {
            $message .= " - Giá mới: " . number_format($changes['price']) . 'đ';
        }
        if (isset($changes['is_active']) && !$changes['is_active']) {
            $message = "Tour {$tour->name} đã tạm dừng hoạt động";
        }

        $this->sendDatabaseNotificationToMany(
            $userIds,
            'tour_updated',
            [
                'title' => 'Tour đã cập nhật',
                'message' => $message,
                'tour_id' => $tour->id,
                'tour_name' => $tour->name,
                'changes' => $changes,
                'url' => route('tours.show', $tour->id),
                'icon' => 'bi-exclamation-circle',
                'color' => 'warning',
            ]
        );
    }

    /**
     * Thông báo khi có coupon mới
     */
    public function notifyCouponCreated($coupon): void
    {
        // Gửi cho tất cả users active
        $userIds = User::where('role', 'user')
            ->pluck('id')
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        $discount = $coupon->discount_type === 'percentage' 
            ? $coupon->discount_value . '%'
            : number_format($coupon->discount_value) . 'đ';

        $this->sendDatabaseNotificationToMany(
            $userIds,
            'new_coupon',
            [
                'title' => 'Mã giảm giá mới',
                'message' => "Mã {$coupon->code} - Giảm {$discount}",
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'discount' => $discount,
                'description' => $coupon->description,
                'url' => route('home'),
                'icon' => 'bi-gift',
                'color' => 'danger',
            ]
        );
    }

    /**
     * Đếm số thông báo database chưa đọc
     */
    public function countDatabaseUnread(User $user): int
    {
        return \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Lấy thông báo database của user
     */
    public function getDatabaseNotifications(User $user, int $limit = 10)
    {
        return \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
