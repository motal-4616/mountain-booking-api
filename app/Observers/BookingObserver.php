<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\UserLevelService;

class BookingObserver
{
    /**
     * Handle the Booking "updated" event.
     * Khi booking chuyển sang completed → tính lại level user
     */
    public function updated(Booking $booking): void
    {
        if ($booking->isDirty('status') && $booking->status === 'completed') {
            $levelService = new UserLevelService();
            $levelService->updateUserLevel($booking->user);
        }
    }
}
