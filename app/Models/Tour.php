<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán giá trị hàng loạt
     */
    protected $fillable = [
        'name',
        'image',
        'gallery',
        'difficulty',
        'min_people',
        'description',
        'overview',
        'itinerary',
        'includes',
        'excludes',
        'highlights',
        'location',
        'altitude',
        'best_time',
        'map_lat',
        'map_lng',
        'is_active',
    ];

    /**
     * Kiểu dữ liệu của các trường
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gallery' => 'array',
            'itinerary' => 'array',
            'altitude' => 'integer',
            'map_lat' => 'decimal:8',
            'map_lng' => 'decimal:8',
        ];
    }

    /**
     * Quan hệ: Tour có nhiều Schedule (lịch trình)
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Quan hệ: Tour có nhiều Review (đánh giá)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Quan hệ: Tour có nhiều Wishlist
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Quan hệ: Chỉ lấy reviews đã được duyệt
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * Accessor: Lấy rating trung bình
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Accessor: Lấy tổng số đánh giá
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Lấy text độ khó tiếng Việt
     */
    public function getDifficultyTextAttribute(): string
    {
        return match($this->difficulty) {
            'easy' => 'Dễ',
            'medium' => 'Trung bình',
            'hard' => 'Khó',
            default => 'Không xác định',
        };
    }

    /**
     * Lấy độ cao đã format
     */
    public function getFormattedAltitudeAttribute(): string
    {
        if (!$this->altitude) return 'Chưa cập nhật';
        return number_format($this->altitude, 0, ',', '.') . 'm';
    }

    /**
     * Lấy danh sách includes dạng array
     */
    public function getIncludesListAttribute(): array
    {
        if (!$this->includes) return [];
        return array_filter(array_map('trim', explode("\n", $this->includes)));
    }

    /**
     * Lấy danh sách excludes dạng array
     */
    public function getExcludesListAttribute(): array
    {
        if (!$this->excludes) return [];
        return array_filter(array_map('trim', explode("\n", $this->excludes)));
    }

    /**
     * Lấy danh sách highlights dạng array
     */
    public function getHighlightsListAttribute(): array
    {
        if (!$this->highlights) return [];
        return array_filter(array_map('trim', explode("\n", $this->highlights)));
    }

    /**
     * Lấy tất cả ảnh (ảnh chính + gallery)
     */
    public function getAllImagesAttribute(): array
    {
        $images = [];
        if ($this->image) {
            $images[] = $this->image;
        }
        if ($this->gallery && is_array($this->gallery)) {
            $images = array_merge($images, $this->gallery);
        }
        return $images;
    }
}
