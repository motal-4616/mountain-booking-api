<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo thanh toán thành công
 * Gửi cho: user (chủ booking), booking_manager
 */
class PaymentSuccessNotification extends Notification
{
    use Queueable;

    protected Booking $booking;
    protected bool $isForAdmin;

    /**
     * @param Booking $booking
     * @param bool $isForAdmin True nếu gửi cho admin/manager
     */
    public function __construct(Booking $booking, bool $isForAdmin = false)
    {
        $this->booking = $booking;
        $this->isForAdmin = $isForAdmin;
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
        $paidAmount = number_format($this->booking->paid_amount, 0, ',', '.');
        
        if ($this->isForAdmin) {
            // Nội dung cho admin/manager
            $userName = $this->booking->user->name ?? $this->booking->contact_name;
            return [
                'type' => 'payment_success_admin',
                'title' => 'Thanh toán thành công',
                'message' => "Đơn #{$this->booking->id} của {$userName} đã thanh toán thành công {$paidAmount}đ.",
                'booking_id' => $this->booking->id,
                'tour_name' => $tourName,
                'customer_name' => $userName,
                'paid_amount' => $this->booking->paid_amount,
                'payment_status' => $this->booking->payment_status,
                'icon' => 'bi-check-circle',
                'color' => 'success',
                'url' => route('admin.bookings.show', $this->booking->id),
            ];
        }
        
        // Nội dung cho user
        return [
            'type' => 'payment_success',
            'title' => 'Thanh toán thành công',
            'message' => "Bạn đã thanh toán thành công {$paidAmount}đ cho đơn đặt vé tour {$tourName}.",
            'booking_id' => $this->booking->id,
            'tour_name' => $tourName,
            'paid_amount' => $this->booking->paid_amount,
            'payment_status' => $this->booking->payment_status,
            'icon' => 'bi-check-circle',
            'color' => 'success',
            'url' => route('bookings.show', $this->booking->id),
        ];
    }
}
