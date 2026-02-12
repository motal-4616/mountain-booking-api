<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'mood',
        'weather',
        'location',
        'latitude',
        'longitude',
        'altitude',
        'images',
        'privacy',
        'tour_id',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'altitude' => 'integer',
        ];
    }

    // ===== Quan hệ =====

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    // ===== Scopes =====

    public function scopePublic($query)
    {
        return $query->where('privacy', 'public');
    }

    public function scopeForFriends($query)
    {
        return $query->whereIn('privacy', ['public', 'friends']);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Nhật ký mà user có thể xem (của mình + bạn bè friends/public + public của người lạ)
     */
    public function scopeVisibleTo($query, int $userId)
    {
        $friendIds = Friendship::getFriendIds($userId);

        return $query->where(function ($q) use ($userId, $friendIds) {
            // Bài viết của chính mình (tất cả privacy)
            $q->where('user_id', $userId)
              // Bài viết public của bất kỳ ai
              ->orWhere('privacy', 'public')
              // Bài viết friends của bạn bè
              ->orWhere(function ($q2) use ($friendIds) {
                  $q2->where('privacy', 'friends')
                     ->whereIn('user_id', $friendIds);
              });
        });
    }

    // ===== Accessors =====

    public function getMoodEmojiAttribute(): string
    {
        return match ($this->mood) {
            'happy' => '😊',
            'excited' => '🤩',
            'peaceful' => '😌',
            'tired' => '😴',
            'sad' => '😢',
            'challenged' => '💪',
            default => '📝',
        };
    }

    public function getMoodTextAttribute(): string
    {
        return match ($this->mood) {
            'happy' => 'Vui vẻ',
            'excited' => 'Phấn khích',
            'peaceful' => 'Bình yên',
            'tired' => 'Mệt mỏi',
            'sad' => 'Buồn',
            'challenged' => 'Thử thách',
            default => 'Không xác định',
        };
    }

    public function getPrivacyTextAttribute(): string
    {
        return match ($this->privacy) {
            'private' => 'Riêng tư',
            'friends' => 'Bạn bè',
            'public' => 'Công khai',
            default => 'Riêng tư',
        };
    }

    public function getPrivacyIconAttribute(): string
    {
        return match ($this->privacy) {
            'private' => 'lock',
            'friends' => 'people',
            'public' => 'public',
            default => 'lock',
        };
    }
}
