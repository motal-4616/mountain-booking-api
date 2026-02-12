<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo khi hoàn tiền hoàn tất (thành công hoặc thất bại)
 */
class RefundCompletedNotification extends Notification
{
    use Queueable;

    public $booking;
    public $success;
    public $message;

    public function __construct(Booking $booking, bool $success, string $message = '')
    {
        $this->booking = $booking;
        $this->success = $success;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $tourName = $this->booking->schedule->tour->name ?? 'Tour';
        $amount = number_format($this->booking->paid_amount, 0, ',', '.') . 'đ';

        if ($this->success) {
            return [
                'type' => 'refund_success',
                'title' => 'Hoàn tiền thành công',
                'message' => "Đơn #{$this->booking->booking_code} đã được hoàn tiền {$amount} thành công cho tour: {$tourName}",
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'tour_name' => $tourName,
                'amount' => $this->booking->paid_amount,
                'icon' => 'bi-check-circle',
                'color' => 'success',
            ];
        }

        return [
            'type' => 'refund_failed',
            'title' => 'Hoàn tiền thất bại',
            'message' => "Hoàn tiền đơn #{$this->booking->booking_code} thất bại: {$this->message}. Vui lòng liên hệ hỗ trợ.",
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'tour_name' => $tourName,
            'amount' => $this->booking->paid_amount,
            'refund_message' => $this->message,
            'icon' => 'bi-x-circle',
            'color' => 'danger',
        ];
    }
}
