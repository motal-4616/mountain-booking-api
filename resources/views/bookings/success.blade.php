@extends('layouts.app')

@section('title', 'Đặt tour thành công')

@section('content')
<style>
/* Success Page Styles */
.success-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.success-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    max-width: 600px;
    width: 100%;
    overflow: hidden;
}

.success-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    padding: 40px 32px;
    text-align: center;
    color: white;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    animation: successPop 0.6s ease-out;
}

@keyframes successPop {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

.success-icon i {
    font-size: 48px;
}

.success-header h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 12px 0;
}

.success-header p {
    font-size: 16px;
    margin: 0;
    opacity: 0.95;
}

/* Steps Progress */
.success-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 32px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.success-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.success-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    background: #10b981;
    color: white;
    border: 2px solid #10b981;
}

.success-step-text {
    font-size: 13px;
    color: #10b981;
    font-weight: 600;
}

.success-step-divider {
    color: #10b981;
    font-size: 20px;
    margin: 0 8px;
    padding-bottom: 20px;
}

/* Booking Details */
.success-body {
    padding: 32px;
}

.booking-info {
    margin-bottom: 24px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.info-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
    text-align: right;
}

.payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.payment-badge.paid {
    background: #d1fae5;
    color: #059669;
}

.payment-badge.partial {
    background: #fef3c7;
    color: #d97706;
}

.total-amount {
    background: #f1f5f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 24px;
}

.total-amount .info-row {
    border: none;
    padding: 8px 0;
}

.total-amount .info-label {
    font-size: 16px;
    color: #1e293b;
    font-weight: 600;
}

.total-amount .info-value {
    font-size: 20px;
    color: #10b981;
    font-weight: 700;
}

/* Actions */
.success-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
}

.btn-action {
    flex: 1;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: #10b981;
    color: white;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

/* Next Steps */
.next-steps {
    background: #eff6ff;
    padding: 24px;
    border-radius: 8px;
    margin-top: 24px;
}

.next-steps h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1e40af;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.next-steps ul {
    margin: 0;
    padding-left: 20px;
}

.next-steps li {
    font-size: 14px;
    color: #1e40af;
    margin-bottom: 8px;
    line-height: 1.6;
}

.next-steps li:last-child {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .success-container {
        padding: 20px 16px;
    }
    
    .success-header {
        padding: 32px 24px;
    }
    
    .success-header h1 {
        font-size: 24px;
    }
    
    .success-body {
        padding: 24px;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .info-row {
        flex-direction: column;
        gap: 8px;
    }
    
    .info-value {
        text-align: left;
    }
}
</style>

<div class="success-container">
    <div class="success-card">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1>Đặt tour thành công!</h1>
            <p>Cảm ơn bạn đã đặt tour tại MountainBook</p>
        </div>

        <!-- Steps Progress -->
        <div class="success-steps">
            <div class="success-step">
                <span class="success-step-number">
                    <i class="bi bi-check"></i>
                </span>
                <span class="success-step-text">Thông tin đặt tour</span>
            </div>
            <div class="success-step-divider">
                <i class="bi bi-arrow-right"></i>
            </div>
            <div class="success-step">
                <span class="success-step-number">
                    <i class="bi bi-check"></i>
                </span>
                <span class="success-step-text">Hoàn tất</span>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="success-body">
            <div class="booking-info">
                <div class="info-row">
                    <span class="info-label">Mã đặt tour</span>
                    <span class="info-value">#{{ $booking->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tour</span>
                    <span class="info-value">{{ $booking->schedule->tour->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày khởi hành</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số lượng</span>
                    <span class="info-value">{{ $booking->quantity }} người</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái thanh toán</span>
                    <span class="info-value">
                        @if($booking->payment_status === 'paid')
                        <span class="payment-badge paid">
                            <i class="bi bi-check-circle-fill"></i>
                            Đã thanh toán đủ
                        </span>
                        @elseif($booking->payment_status === 'partial')
                        <span class="payment-badge partial">
                            <i class="bi bi-clock-fill"></i>
                            Đã đặt cọc {{ $booking->deposit_percent }}%
                        </span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="total-amount">
                <div class="info-row">
                    <span class="info-label">
                        @if($booking->payment_status === 'partial')
                        Số tiền đã thanh toán
                        @else
                        Tổng tiền
                        @endif
                    </span>
                    <span class="info-value">{{ number_format($booking->paid_amount, 0, ',', '.') }}đ</span>
                </div>
                @if($booking->payment_status === 'partial')
                <div class="info-row">
                    <span class="info-label" style="color: #64748b; font-size: 14px;">Còn lại</span>
                    <span class="info-value" style="font-size: 16px; color: #64748b;">{{ number_format($booking->total_amount - $booking->paid_amount, 0, ',', '.') }}đ</span>
                </div>
                @endif
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>
                    <i class="bi bi-info-circle-fill"></i>
                    Bước tiếp theo
                </h3>
                <ul>
                    <li>Chúng tôi sẽ gửi email xác nhận đến <strong>{{ $booking->contact_email }}</strong></li>
                    <li>Vui lòng kiểm tra email và thông tin liên hệ</li>
                    @if($booking->payment_status === 'partial')
                    <li>Thanh toán số tiền còn lại trước {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->subDays(3)->format('d/m/Y') }}</li>
                    @endif
                    <li>Chuẩn bị hành lý và giấy tờ tùy thân trước ngày khởi hành</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="success-actions">
                <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-primary">
                    <i class="bi bi-receipt"></i>
                    Xem chi tiết đơn hàng
                </a>
                <a href="{{ route('tours.index') }}" class="btn-action btn-secondary">
                    <i class="bi bi-compass"></i>
                    Khám phá thêm
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
