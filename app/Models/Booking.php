<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán giá trị hàng loạt
     * Đơn giản hóa: Chỉ giữ thông tin đơn hàng, thanh toán chuyển sang booking_payments
     */
    protected $fillable = [
        'user_id',
        'schedule_id',
        'coupon_id',
        'quantity',
        'contact_name',
        'contact_phone',
        'contact_email',
        'note',
        'total_amount',      // Giá gốc (quantity * price)
        'discount_amount',   // Số tiền giảm giá
        'final_price',       // Giá cuối cùng = total_amount - discount_amount
        'status',            // pending, confirmed, completed, cancelled, refund_processing, refunded
        'cancellation_reason', // Lý do hủy đơn
        'cancelled_by',      // User ID người hủy
        'cancelled_at',      // Thời gian hủy
        'refund_status',     // processing, success, failed
        'refund_message',    // Message từ VNPay
        'refund_transaction_ref', // Mã giao dịch hoàn tiền
        'refund_processed_at', // Thời gian xử lý hoàn tiền
    ];

    /**
     * Kiểu dữ liệu của các trường
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:0',
            'discount_amount' => 'decimal:0',
            'final_price' => 'decimal:0',
            'cancelled_at' => 'datetime',
            'refund_processed_at' => 'datetime',
        ];
    }

    /**
     * Các accessor được append tự động
     */
    protected $appends = [
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'payment_method',
        'payment_method_text',
        'booking_code',
        'can_cancel',
        'can_review',
        'status_badge_class',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Booking thuộc về User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Booking thuộc về Schedule
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Booking có thể có Coupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Booking có nhiều lần thanh toán
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Lấy các thanh toán thành công
     */
    public function successfulPayments(): HasMany
    {
        return $this->hasMany(BookingPayment::class)->where('status', 'success');
    }

    /**
     * Người hủy đơn (User)
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ==================== PAYMENT HELPERS ====================

    /**
     * Tổng số tiền đã thanh toán thành công
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->successfulPayments()->sum('amount');
    }

    /**
     * Số tiền còn phải thanh toán
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->final_price - $this->paid_amount);
    }

    /**
     * Trạng thái thanh toán (tính từ payments)
     */
    public function getPaymentStatusAttribute(): string
    {
        // Kiểm tra có payment refunded không (ưu tiên cao nhất)
        $hasRefund = $this->payments()->where('status', 'refunded')->exists();
        if ($hasRefund) {
            return 'refunded';
        }

        $paidAmount = $this->paid_amount;
        $finalPrice = $this->final_price;

        if ($paidAmount <= 0) {
            return 'unpaid';
        }

        if ($paidAmount >= $finalPrice) {
            return 'paid';
        }

        return 'partial'; // Đã đặt cọc
    }

    /**
     * Kiểm tra đã thanh toán đủ chưa
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Kiểm tra đã đặt cọc chưa
     */
    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    /**
     * Kiểm tra có coupon không
     */
    public function hasCoupon(): bool
    {
        return $this->coupon_id !== null;
    }

    // ==================== STATUS TEXT & BADGES ====================

    /**
     * Text trạng thái đơn hàng
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refund_processing' => 'Đang xử lý hoàn tiền',
            'refunded' => 'Đã hoàn tiền',
            default => 'Không xác định',
        };
    }

    /**
     * Badge CSS cho trạng thái đơn hàng
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refund_processing' => 'warning',
            'refunded' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Text trạng thái thanh toán
     */
    public function getPaymentStatusTextAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid' => 'Chưa thanh toán',
            'partial' => 'Đã đặt cọc',
            'paid' => 'Đã thanh toán đủ',
            'refunded' => 'Đã hoàn tiền',
            default => 'Chưa thanh toán',
        };
    }

    /**
     * Badge CSS cho trạng thái thanh toán
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid' => 'danger',
            'partial' => 'warning',
            'paid' => 'success',
            'refunded' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Lấy phương thức thanh toán đầu tiên (từ payment success đầu tiên)
     */
    public function getPaymentMethodAttribute(): ?string
    {
        $firstPayment = $this->payments()->where('status', 'success')->orderBy('created_at')->first();
        return $firstPayment ? $firstPayment->payment_method : null;
    }

    /**
     * Text phương thức thanh toán
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return match($this->payment_method) {
            'vnpay' => 'VNPay',
            'cash' => 'Tiền mặt',
            'transfer' => 'Chuyển khoản',
            default => 'Chưa thanh toán',
        };
    }

    // ==================== FORMATTED VALUES ====================

    /**
     * Format tổng tiền
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format((float)$this->total_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Format giá cuối cùng (sau giảm)
     */
    public function getFormattedFinalPriceAttribute(): string
    {
        return number_format((float)$this->final_price, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Format số tiền giảm
     */
    public function getFormattedDiscountAmountAttribute(): string
    {
        return number_format((float)($this->discount_amount ?? 0), 0, ',', '.') . ' VNĐ';
    }

    /**
     * Format số tiền đã thanh toán
     */
    public function getFormattedPaidAmountAttribute(): string
    {
        return number_format((float)$this->paid_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Format số tiền còn lại
     */
    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format((float)$this->remaining_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Alias cho formatted_final_price (backward compatible)
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->formatted_final_price;
    }

    // ==================== BOOKING CODES & PERMISSIONS ====================

    /**
     * Generate booking code
     */
    public function getBookingCodeAttribute(): string
    {
        return 'BK' . date('Ymd', strtotime($this->created_at)) . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if booking can be cancelled
     */
    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if booking is in refund processing
     */
    public function isRefundProcessing(): bool
    {
        return $this->status === 'refund_processing';
    }

    /**
     * Check if user can review this booking
     */
    public function getCanReviewAttribute(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        // Check if already reviewed
        $hasReview = Review::where('user_id', $this->user_id)
            ->where('tour_id', $this->schedule->tour_id)
            ->exists();

        return !$hasReview;
    }

    /**
     * Get status badge CSS class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'confirmed' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            'refund_processing' => 'badge-warning',
            'refunded' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Lấy booking pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Lấy booking đã xác nhận
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Lấy booking hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Lấy booking đã hủy
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
