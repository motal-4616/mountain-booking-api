<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Thông báo booking đã được xác nhận
 * Gửi cho: user (chủ booking)
 */
class BookingConfirmedNotification extends Notification
{
    use Queueable;

    protected Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tourName = $this->booking->schedule?->tour?->name ?? 'Tour';
        $startDate = $this->booking->schedule?->start_date ?? '';

        return [
            'type' => 'booking_confirmed',
            'title' => 'Booking đã được xác nhận',
            'message' => "Đơn đặt vé tour {$tourName} của bạn đã được xác nhận. Hãy chuẩn bị cho chuyến đi!",
            'booking_id' => $this->booking->id,
            'tour_name' => $tourName,
            'start_date' => $startDate,
            'icon' => 'bi-check-circle',
            'color' => 'success',
        ];
    }
}
