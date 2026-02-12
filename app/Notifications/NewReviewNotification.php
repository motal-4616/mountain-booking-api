<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo có đánh giá mới cần duyệt
 * Gửi cho: booking_manager
 */
class NewReviewNotification extends Notification
{
    use Queueable;

    protected Review $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
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
        $tourName = $this->review->tour->name ?? 'Tour';
        $userName = $this->review->user->name ?? 'Khách';
        
        return [
            'type' => 'new_review',
            'title' => 'Đánh giá mới cần duyệt',
            'message' => "{$userName} đã đánh giá {$this->review->rating} sao cho tour {$tourName}. Vui lòng kiểm tra và duyệt.",
            'review_id' => $this->review->id,
            'tour_id' => $this->review->tour_id,
            'tour_name' => $tourName,
            'user_name' => $userName,
            'rating' => $this->review->rating,
            'comment_preview' => mb_substr($this->review->comment ?? '', 0, 100),
            'icon' => 'bi-star',
            'color' => 'info',
            'url' => route('admin.reviews.show', $this->review->id),
        ];
    }
}
