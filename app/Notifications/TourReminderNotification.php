<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TourReminderNotification extends Notification
{
    use Queueable;

    protected Booking $booking;
    protected int $daysUntil;

    public function __construct(Booking $booking, int $daysUntil)
    {
        $this->booking = $booking;
        $this->daysUntil = $daysUntil;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $tourName = $this->booking->schedule?->tour?->name ?? 'Tour';
        $departureDate = $this->booking->schedule?->departure_date;
        $formattedDate = $departureDate
            ? \Carbon\Carbon::parse($departureDate)->format('d/m/Y')
            : '';

        if ($this->daysUntil === 1) {
            $title = "⏰ Ngày mai bạn khởi hành!";
            $message = "Tour \"{$tourName}\" sẽ khởi hành vào ngày mai ({$formattedDate}). Hãy chuẩn bị hành lý và kiểm tra thông tin booking nhé!";
        } elseif ($this->daysUntil === 3) {
            $title = "🏔️ Còn 3 ngày nữa là khởi hành!";
            $message = "Tour \"{$tourName}\" sẽ khởi hành vào ngày {$formattedDate}. Đừng quên chuẩn bị mọi thứ cần thiết!";
        } else {
            $title = "📅 Sắp đến ngày khởi hành";
            $message = "Tour \"{$tourName}\" sẽ khởi hành sau {$this->daysUntil} ngày nữa ({$formattedDate}).";
        }

        return [
            'type' => 'tour_reminder',
            'title' => $title,
            'message' => $message,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'tour_name' => $tourName,
            'departure_date' => $departureDate,
            'days_until' => $this->daysUntil,
        ];
    }
}
