<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán giá trị hàng loạt
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    /**
     * Kiểu dữ liệu của các trường
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:0',
            'min_order_amount' => 'decimal:0',
            'max_discount' => 'decimal:0',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Appended attributes
     */
    protected $appends = [
        'is_valid',
    ];

    /**
     * Quan hệ: Coupon được tạo bởi User (admin)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Quan hệ: Coupon có nhiều Bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope: Chỉ lấy coupon đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Lọc coupon còn hiệu lực (trong thời gian)
     */
    public function scopeValid($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }

    /**
     * Scope: Lọc coupon còn lượt sử dụng
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereColumn('used_count', '<', 'usage_limit');
        });
    }

    /**
     * Kiểm tra coupon có hợp lệ không
     */
    public function isValid(): bool
    {
        // Kiểm tra trạng thái active
        if (!$this->is_active) {
            return false;
        }

        // Kiểm tra thời gian hiệu lực
        $today = now()->toDateString();
        if ($this->start_date > $today || $this->end_date < $today) {
            return false;
        }

        // Kiểm tra còn lượt sử dụng
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Accessor for is_valid attribute
     */
    public function getIsValidAttribute(): bool
    {
        return $this->isValid();
    }

    /**
     * Kiểm tra có thể áp dụng cho đơn hàng không
     */
    public function canApplyToOrder(float $orderAmount): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($this->min_order_amount > 0 && $orderAmount < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Tính số tiền được giảm
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->canApplyToOrder($orderAmount)) {
            return 0;
        }

        if ($this->type === 'percent') {
            $discount = $orderAmount * ($this->value / 100);
            
            // Áp dụng giới hạn max_discount nếu có
            if ($this->max_discount !== null && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
        } else {
            // Loại fixed - giảm số tiền cố định
            $discount = $this->value;
        }

        // Không được giảm quá tổng đơn hàng
        return min($discount, $orderAmount);
    }

    /**
     * Tăng số lượt sử dụng
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Giảm số lượt sử dụng (khi hủy booking)
     */
    public function decrementUsage(): void
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }

    /**
     * Lấy text loại giảm giá
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'percent' => 'Phần trăm',
            'fixed' => 'Số tiền cố định',
            default => 'Không xác định',
        };
    }

    /**
     * Lấy text hiển thị giá trị giảm
     */
    public function getValueDisplayAttribute(): string
    {
        if ($this->type === 'percent') {
            return $this->value . '%';
        }
        return number_format($this->value, 0, ',', '.') . 'đ';
    }

    /**
     * Lấy text trạng thái
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Đã tắt';
        }

        $today = now()->toDateString();
        
        if ($this->start_date > $today) {
            return 'Chưa bắt đầu';
        }
        
        if ($this->end_date < $today) {
            return 'Đã hết hạn';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'Hết lượt sử dụng';
        }

        return 'Đang hoạt động';
    }

    /**
     * Lấy class CSS cho badge trạng thái
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status_text) {
            'Đang hoạt động' => 'bg-success',
            'Chưa bắt đầu' => 'bg-info',
            'Đã hết hạn' => 'bg-secondary',
            'Hết lượt sử dụng' => 'bg-warning',
            'Đã tắt' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Lấy số lượt còn lại
     */
    public function getRemainingUsageAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null; // Không giới hạn
        }
        return max(0, $this->usage_limit - $this->used_count);
    }
}
