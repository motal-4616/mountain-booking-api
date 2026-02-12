<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPayment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_type',     // deposit, full, remaining
        'payment_method',   // vnpay, cash, transfer
        'amount',
        'status',           // pending, success, failed, refunded
        'transaction_ref',  // vnp_TxnRef (mã đơn hàng merchant)
        'vnp_transaction_no', // vnp_TransactionNo (mã giao dịch VNPay)
        'vnp_pay_date',     // vnp_PayDate (ngày thanh toán gốc từ VNPay - dùng cho refund)
        'vnpay_response_code',
        'confirmed_by',
        'confirmed_at',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Booking liên quan
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Admin xác nhận
     */
    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Lấy text loại thanh toán
     */
    public function getPaymentTypeTextAttribute(): string
    {
        return match($this->payment_type) {
            'deposit' => 'Đặt cọc',
            'full' => 'Thanh toán đủ',
            'remaining' => 'Thanh toán phần còn lại',
            default => 'Không xác định',
        };
    }

    /**
     * Lấy text phương thức
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return match($this->payment_method) {
            'vnpay' => 'VNPay',
            'cash' => 'Tiền mặt',
            'transfer' => 'Chuyển khoản',
            default => 'Không xác định',
        };
    }

    /**
     * Lấy text trạng thái
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Đang chờ',
            'success' => 'Đã thanh toán',
            'failed' => 'Thất bại',
            'refunded' => 'Đã hoàn tiền',
            default => 'Không xác định',
        };
    }

    /**
     * Lấy badge CSS
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'success' => 'success',
            'failed' => 'danger',
            'refunded' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Format số tiền
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . 'đ';
    }

    /**
     * Scope: chỉ lấy thanh toán thành công
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: lấy theo booking
     */
    public function scopeForBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }
}
