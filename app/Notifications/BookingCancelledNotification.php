<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Thông báo khi đơn booking bị hủy
 */
class BookingCancelledNotification extends Notification
{
    use Queueable;

    public $booking;
    public $cancelledByUser;
    public $isAdminCancelled;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, User $cancelledByUser, bool $isAdminCancelled = false)
    {
        $this->booking = $booking;
        $this->cancelledByUser = $cancelledByUser;
        $this->isAdminCancelled = $isAdminCancelled;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $cancellerName = $this->isAdminCancelled 
            ? 'Quản trị viên ' . $this->cancelledByUser->name
            : 'Khách hàng ' . $this->cancelledByUser->name;

        return [
            'type' => 'booking_cancelled',
            'title' => 'Đơn đặt vé #' . $this->booking->id . ' đã bị hủy',
            'message' => $cancellerName . ' đã hủy đơn đặt vé cho tour: ' . $this->booking->schedule->tour->name,
            'booking_id' => $this->booking->id,
            'tour_name' => $this->booking->schedule->tour->name,
            'cancelled_by' => $this->cancelledByUser->name,
            'cancelled_by_id' => $this->cancelledByUser->id,
            'is_admin_cancelled' => $this->isAdminCancelled,
            'cancellation_reason' => $this->booking->cancellation_reason,
            'action_url' => $this->isAdminCancelled 
                ? route('bookings.show', $this->booking->id)
                : route('admin.bookings.show', $this->booking->id),
        ];
    }
}
