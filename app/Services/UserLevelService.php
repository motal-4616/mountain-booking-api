<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLevel;
use App\Models\Booking;
use App\Models\Review;
use App\Models\BlogPost;
use App\Notifications\LevelUpNotification;
use Illuminate\Support\Facades\Log;

class UserLevelService
{
    /**
     * Tính toán level hiện tại của user dựa trên thành tích
     */
    public function calculateLevel(User $user): array
    {
        $completedTours = Booking::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $approvedReviews = Review::where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $publishedBlogs = BlogPost::where('user_id', $user->id)
            ->where('status', 'published')
            ->count();

        // Tìm level cao nhất mà user đủ điều kiện
        $level = UserLevel::where('required_tours', '<=', $completedTours)
            ->where('required_reviews', '<=', $approvedReviews)
            ->where('required_blogs', '<=', $publishedBlogs)
            ->orderBy('level', 'desc')
            ->first();

        if (!$level) {
            $level = UserLevel::where('level', 1)->first();
        }

        $nextLevel = UserLevel::where('level', ($level ? $level->level : 1) + 1)->first();

        return [
            'level' => $level,
            'stats' => [
                'completed_tours' => $completedTours,
                'approved_reviews' => $approvedReviews,
                'published_blogs' => $publishedBlogs,
            ],
            'next_level' => $nextLevel,
            'progress' => $nextLevel ? $this->calculateProgress($completedTours, $approvedReviews, $publishedBlogs, $nextLevel) : 100,
        ];
    }

    /**
     * Tính phần trăm tiến độ tới level tiếp theo
     */
    protected function calculateProgress(int $tours, int $reviews, int $blogs, UserLevel $nextLevel): float
    {
        $tourProgress = $nextLevel->required_tours > 0
            ? min(100, ($tours / $nextLevel->required_tours) * 100)
            : 100;

        $reviewProgress = $nextLevel->required_reviews > 0
            ? min(100, ($reviews / $nextLevel->required_reviews) * 100)
            : 100;

        $blogProgress = $nextLevel->required_blogs > 0
            ? min(100, ($blogs / $nextLevel->required_blogs) * 100)
            : 100;

        return round(($tourProgress + $reviewProgress + $blogProgress) / 3, 1);
    }

    /**
     * Cập nhật level cho user (gọi khi có thay đổi: booking completed, review approved, blog published)
     */
    public function updateUserLevel(User $user): void
    {
        try {
            $result = $this->calculateLevel($user);
            $level = $result['level'];

            if (!$level) {
                return;
            }

            $oldLevel = $user->current_level ?? 1;

            if ($level->level !== $oldLevel) {
                $user->update([
                    'current_level' => $level->level,
                    'level_discount' => $level->discount_percent,
                ]);

                // Gửi notification nếu lên level
                if ($level->level > $oldLevel) {
                    try {
                        $user->notify(new LevelUpNotification($level));
                    } catch (\Exception $e) {
                        Log::error('Failed to send level up notification: ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update user level: ' . $e->getMessage());
        }
    }
}
