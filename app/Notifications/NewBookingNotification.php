<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo có booking mới
 * Gửi cho: booking_manager
 */
class NewBookingNotification extends Notification
{
    use Queueable;

    protected Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        
        return [
            'type' => 'new_booking',
            'title' => 'Đơn đặt vé mới',
            'message' => "Có đơn đặt vé mới #{$this->booking->id} từ khách hàng {$userName} cho tour {$tourName}.",
            'booking_id' => $this->booking->id,
            'tour_name' => $tourName,
            'customer_name' => $userName,
            'quantity' => $this->booking->quantity,
            'total_amount' => $this->booking->total_amount,
            'icon' => 'bi-ticket-perforated',
            'color' => 'primary',
            'url' => route('admin.bookings.show', $this->booking->id),
        ];
    }
}
