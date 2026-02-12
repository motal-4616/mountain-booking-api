<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo khi đơn đang được xử lý hoàn tiền
 */
class RefundProcessingNotification extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $tourName = $this->booking->schedule->tour->name ?? 'Tour';
        $amount = number_format($this->booking->paid_amount, 0, ',', '.') . 'đ';

        return [
            'type' => 'refund_processing',
            'title' => 'Đang xử lý hoàn tiền',
            'message' => "Đơn #{$this->booking->booking_code} đang được xử lý hoàn tiền {$amount} cho tour: {$tourName}",
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'tour_name' => $tourName,
            'amount' => $this->booking->paid_amount,
            'icon' => 'bi-hourglass-split',
            'color' => 'warning',
        ];
    }
}
