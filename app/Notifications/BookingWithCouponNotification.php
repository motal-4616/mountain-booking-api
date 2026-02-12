<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo booking có áp dụng mã giảm giá
 * Gửi cho: booking_manager
 */
class BookingWithCouponNotification extends Notification
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
        $couponCode = $this->booking->coupon->code ?? 'N/A';
        $discountAmount = number_format($this->booking->discount_amount, 0, ',', '.');
        
        return [
            'type' => 'booking_with_coupon',
            'title' => 'Đơn có mã giảm giá',
            'message' => "Đơn #{$this->booking->id} của {$userName} đã áp dụng mã {$couponCode} (giảm {$discountAmount}đ).",
            'booking_id' => $this->booking->id,
            'tour_name' => $tourName,
            'customer_name' => $userName,
            'coupon_code' => $couponCode,
            'discount_amount' => $this->booking->discount_amount,
            'final_price' => $this->booking->final_price,
            'icon' => 'bi-tag',
            'color' => 'warning',
            'url' => route('admin.bookings.show', $this->booking->id),
        ];
    }
}
