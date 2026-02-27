<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\UserLevelService;

class ReviewObserver
{
    /**
     * Handle the Review "updated" event.
     * Khi review được approve → tính lại level user
     */
    public function updated(Review $review): void
    {
        if ($review->isDirty('status') && $review->status === 'approved') {
            $levelService = new UserLevelService();
            $levelService->updateUserLevel($review->user);
        }
    }

    /**
     * Handle the Review "created" event.
     * Khi review mới tạo với status approved → tính lại level
     */
    public function created(Review $review): void
    {
        if ($review->status === 'approved') {
            $levelService = new UserLevelService();
            $levelService->updateUserLevel($review->user);
        }
    }
}
