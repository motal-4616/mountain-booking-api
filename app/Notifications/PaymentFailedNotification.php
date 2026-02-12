<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo thanh toán thất bại
 * Gửi cho: user, booking_manager, super_admin
 */
class PaymentFailedNotification extends Notification
{
    use Queueable;

    protected Booking $booking;
    protected string $errorMessage;
    protected bool $isForManager;
    protected bool $isForSuperAdmin;

    /**
     * @param Booking $booking
     * @param string $errorMessage
     * @param bool $isForManager True nếu gửi cho booking_manager
     * @param bool $isForSuperAdmin True nếu gửi cho super_admin
     */
    public function __construct(
        Booking $booking, 
        string $errorMessage = '', 
        bool $isForManager = false,
        bool $isForSuperAdmin = false
    ) {
        $this->booking = $booking;
        $this->errorMessage = $errorMessage;
        $this->isForManager = $isForManager;
        $this->isForSuperAdmin = $isForSuperAdmin;
    }

    /**
     * Kênh gửi thông báo
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Dữ liệu lưu vào database
     */
    public function toArray(object $notifiable): array
    {
        $tourName = $this->booking->schedule->tour->name ?? 'Tour';
        $userName = $this->booking->user->name ?? $this->booking->contact_name;
        
        if ($this->isForSuperAdmin) {
            // Nội dung cho super_admin (chi tiết hơn để debug)
            return [
                'type' => 'payment_failed_system',
                'title' => '⚠️ Lỗi thanh toán',
                'message' => "Thanh toán thất bại cho đơn #{$this->booking->id}. Error: {$this->errorMessage}. Cần kiểm tra hệ thống thanh toán.",
                'booking_id' => $this->booking->id,
                'tour_name' => $tourName,
                'customer_name' => $userName,
                'error_message' => $this->errorMessage,
                'icon' => 'bi-exclamation-triangle',
                'color' => 'danger',
                'priority' => 'high',
                'url' => route('admin.bookings.show', $this->booking->id),
            ];
        }
        
        if ($this->isForManager) {
            // Nội dung cho booking_manager
            return [
                'type' => 'payment_failed_admin',
                'title' => 'Thanh toán thất bại',
                'message' => "Đơn #{$this->booking->id} của {$userName} thanh toán thất bại. Liên hệ khách hàng để hỗ trợ.",
                'booking_id' => $this->booking->id,
                'tour_name' => $tourName,
                'customer_name' => $userName,
                'customer_phone' => $this->booking->contact_phone,
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'url' => route('admin.bookings.show', $this->booking->id),
            ];
        }
        
        // Nội dung cho user
        return [
            'type' => 'payment_failed',
            'title' => 'Thanh toán không thành công',
            'message' => "Thanh toán cho đơn đặt vé tour {$tourName} không thành công. Vui lòng thử lại hoặc liên hệ hỗ trợ.",
            'booking_id' => $this->booking->id,
            'tour_name' => $tourName,
            'icon' => 'bi-x-circle',
            'color' => 'danger',
            'url' => route('bookings.show', $this->booking->id),
        ];
    }
}
