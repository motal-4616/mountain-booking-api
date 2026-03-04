@extends('layouts.app')

@section('title', 'Xác nhận đặt tour')

@push('styles')
<style>
/* Main Layout */
.confirm-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.confirm-header {
    margin-bottom: 32px;
}

.confirm-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.confirm-header p {
    color: #64748b;
    margin: 0;
}

/* Steps */
.confirm-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 32px;
}

.confirm-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.confirm-step .step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    background: #f1f5f9;
    color: #94a3b8;
    border: 2px solid #e2e8f0;
    transition: all 0.3s;
}

.confirm-step .step-text {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    transition: all 0.3s;
}

.confirm-step.completed .step-number {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.confirm-step.completed .step-text {
    color: #10b981;
    font-weight: 600;
}

.confirm-step.active .step-number {
    background: #10b981;
    color: white;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}

.confirm-step.active .step-text {
    color: #10b981;
    font-weight: 600;
}

.confirm-step-divider {
    color: #cbd5e1;
    font-size: 20px;
    margin: 0 8px;
    padding-bottom: 20px;
}

.confirm-step.completed + .confirm-step-divider {
    color: #10b981;
}

/* Two Column Layout */
.confirm-layout {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 40px;
    align-items: start;
}

/* Left Column */
.confirm-left {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Section Card */
.confirm-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #1a1a1a;
}

.section-header .edit-link {
    font-size: 14px;
    color: #10b981;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.section-header .edit-link:hover {
    color: #059669;
}

.section-body {
    padding: 24px;
}

/* Tour Info */
.tour-info-card {
    display: flex;
    gap: 20px;
}

.tour-info-image {
    width: 140px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.tour-info-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tour-info-image .no-image {
    width: 100%;
    height: 100%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 24px;
}

.tour-info-details {
    flex: 1;
    min-width: 0;
}

.tour-info-details h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.tour-detail-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #64748b;
    margin-bottom: 6px;
}

.tour-detail-row i {
    width: 18px;
    text-align: center;
    color: #94a3b8;
}

.tour-detail-row strong {
    color: #1e293b;
}

/* Customer Info */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item .info-label {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item .info-value {
    font-size: 15px;
    color: #1e293b;
    font-weight: 500;
}

/* Payment Type Badge */
.payment-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.payment-type-badge.full {
    background: #d1fae5;
    color: #059669;
}

.payment-type-badge.deposit {
    background: #fef3c7;
    color: #d97706;
}

/* Right Column - Order Summary */
.confirm-right {
    position: sticky;
    top: 100px;
}

.order-summary {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.order-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.order-header h3 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #1a1a1a;
}

/* Order Totals */
.order-totals {
    padding: 20px 24px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 14px;
}

.total-row .label {
    color: #64748b;
}

.total-row .value {
    color: #1a1a1a;
    font-weight: 500;
}

.total-row.discount {
    color: #10b981;
}

.total-row.discount .value {
    color: #10b981;
    font-weight: 600;
}

.total-row.deposit {
    padding-top: 12px;
    border-top: 1px dashed #e2e8f0;
    margin-top: 12px;
}

.total-row.remaining .label {
    color: #f59e0b;
}

.total-row.remaining .value {
    color: #f59e0b;
    font-weight: 500;
}

.total-row.grand-total {
    padding-top: 16px;
    border-top: 2px solid #e2e8f0;
    margin-top: 16px;
    margin-bottom: 0;
}

.total-row.grand-total .label {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
}

.total-row.grand-total .value {
    font-size: 20px;
    font-weight: 700;
    color: #10b981;
}

/* Agreement */
.agreement-section {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
}

.agreement-check {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}

.agreement-check input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    accent-color: #10b981;
    flex-shrink: 0;
}

.agreement-check span {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}

.agreement-check a {
    color: #10b981;
    text-decoration: underline;
}

/* Footer Actions */
.confirm-footer {
    padding: 24px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    color: #64748b;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back:hover {
    color: #1a1a1a;
}

.btn-pay {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-pay:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.btn-pay:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-pay i {
    font-size: 18px;
}

/* Payment Note */
.payment-note {
    padding: 16px 20px;
    background: #eff6ff;
    border-radius: 8px;
    display: flex;
    gap: 12px;
    margin: 0 24px 20px;
}

.payment-note i {
    color: #3b82f6;
    font-size: 18px;
    flex-shrink: 0;
    margin-top: 2px;
}

.payment-note p {
    font-size: 13px;
    color: #1e40af;
    margin: 0;
    line-height: 1.5;
}

/* VNPay badge */
.vnpay-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #0066b3 0%, #004494 100%);
    border-radius: 8px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    margin: 0 24px 16px;
}

.vnpay-badge i {
    font-size: 16px;
}

/* Security badges */
.security-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px 24px;
    color: #94a3b8;
    font-size: 12px;
    border-top: 1px solid #e2e8f0;
}

.security-info i {
    font-size: 14px;
}

/* Alert */
.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
}

.alert-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}

/* Responsive */
@media (max-width: 992px) {
    .confirm-layout {
        grid-template-columns: 1fr;
    }
    
    .confirm-right {
        position: static;
    }
}

@media (max-width: 576px) {
    .confirm-container {
        padding: 20px 16px;
    }
    
    .tour-info-card {
        flex-direction: column;
    }
    
    .tour-info-image {
        width: 100%;
        height: 160px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .confirm-steps {
        flex-wrap: wrap;
        gap: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="confirm-container">
    <!-- Header -->
    <div class="confirm-header">
        <h1>Xác nhận đơn hàng</h1>
        <p>Vui lòng kiểm tra lại thông tin trước khi thanh toán</p>
    </div>

    <!-- Steps - 3 bước -->
    <div class="confirm-steps">
        <div class="confirm-step completed">
            <span class="step-number"><i class="bi bi-check"></i></span>
            <span class="step-text">Thông tin đặt tour</span>
        </div>
        <div class="confirm-step-divider" style="color: #10b981;">
            <i class="bi bi-arrow-right"></i>
        </div>
        <div class="confirm-step active">
            <span class="step-number">2</span>
            <span class="step-text">Xác nhận</span>
        </div>
        <div class="confirm-step-divider">
            <i class="bi bi-arrow-right"></i>
        </div>
        <div class="confirm-step">
            <span class="step-number">3</span>
            <span class="step-text">Hoàn tất</span>
        </div>
    </div>

    <div class="confirm-layout">
        <!-- Left Column -->
        <div class="confirm-left">
            <!-- Tour Info Section -->
            <div class="confirm-section">
                <div class="section-header">
                    <h2><i class="bi bi-mountain me-2"></i>Thông tin tour</h2>
                </div>
                <div class="section-body">
                    <div class="tour-info-card">
                        <div class="tour-info-image">
                            @if($tour->image)
                            <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}">
                            @else
                            <div class="no-image">
                                <i class="bi bi-image"></i>
                            </div>
                            @endif
                        </div>
                        <div class="tour-info-details">
                            <h3>{{ $tour->name }}</h3>
                            <div class="tour-detail-row">
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $tour->location }}</span>
                            </div>
                            <div class="tour-detail-row">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    Ngày khởi hành: <strong>{{ \Carbon\Carbon::parse($schedule->departure_date)->format('d/m/Y') }}</strong>
                                </span>
                            </div>
                            @if($schedule->end_date)
                            <div class="tour-detail-row">
                                <i class="bi bi-calendar-check"></i>
                                <span>
                                    Ngày kết thúc: <strong>{{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}</strong>
                                </span>
                            </div>
                            @endif
                            <div class="tour-detail-row">
                                <i class="bi bi-people"></i>
                                <span>Số lượng: <strong>{{ $booking->quantity }} người</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info Section -->
            <div class="confirm-section">
                <div class="section-header">
                    <h2><i class="bi bi-person me-2"></i>Thông tin khách hàng</h2>
                </div>
                <div class="section-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Họ và tên</span>
                            <span class="info-value">{{ $checkoutInfo['customer_name'] ?? $booking->contact_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Số điện thoại</span>
                            <span class="info-value">{{ $checkoutInfo['customer_phone'] ?? $booking->contact_phone }}</span>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $checkoutInfo['customer_email'] ?? $booking->contact_email }}</span>
                        </div>
                        @if(!empty($checkoutInfo['notes']) || $booking->note)
                        <div class="info-item full-width">
                            <span class="info-label">Ghi chú</span>
                            <span class="info-value">{{ $checkoutInfo['notes'] ?? $booking->note }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Info Section -->
            <div class="confirm-section">
                <div class="section-header">
                    <h2><i class="bi bi-credit-card me-2"></i>Hình thức thanh toán</h2>
                </div>
                <div class="section-body">
                    @if(($checkoutInfo['payment_type'] ?? 'full') === 'full')
                    <span class="payment-type-badge full">
                        <i class="bi bi-check-circle-fill"></i>
                        Thanh toán 100%
                    </span>
                    <p style="font-size: 14px; color: #64748b; margin: 12px 0 0;">
                        Thanh toán toàn bộ {{ number_format($finalPrice, 0, ',', '.') }}đ qua VNPay
                    </p>
                    @else
                    <span class="payment-type-badge deposit">
                        <i class="bi bi-clock-fill"></i>
                        Đặt cọc {{ $checkoutInfo['deposit_percent'] ?? 30 }}%
                    </span>
                    <p style="font-size: 14px; color: #64748b; margin: 12px 0 0;">
                        Thanh toán trước {{ number_format($amountToPay, 0, ',', '.') }}đ, 
                        còn lại {{ number_format($remainingAmount, 0, ',', '.') }}đ thanh toán tại điểm tập kết
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="confirm-right">
            <form action="{{ route('bookings.payment.vnpay', $booking->id) }}" method="POST" id="confirmForm">
                @csrf
                <input type="hidden" name="payment_type" value="{{ $checkoutInfo['payment_type'] ?? 'full' }}">
                @if(($checkoutInfo['payment_type'] ?? 'full') === 'deposit')
                <input type="hidden" name="deposit_percent" value="{{ $checkoutInfo['deposit_percent'] ?? 30 }}">
                @endif

                <div class="order-summary">
                    <div class="order-header">
                        <h3>Tóm tắt đơn hàng</h3>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span class="label">Giá tour (x{{ $booking->quantity }} người)</span>
                            <span class="value">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</span>
                        </div>

                        @if($booking->discount_amount > 0)
                        <div class="total-row discount">
                            <span class="label">
                                <i class="bi bi-tag-fill me-1"></i>Giảm giá
                                @if($booking->coupon)
                                ({{ $booking->coupon->code }})
                                @endif
                            </span>
                            <span class="value">-{{ number_format($booking->discount_amount, 0, ',', '.') }}đ</span>
                        </div>
                        @endif

                        <div class="total-row">
                            <span class="label">Tổng tiền sau giảm</span>
                            <span class="value">{{ number_format($finalPrice, 0, ',', '.') }}đ</span>
                        </div>

                        @if(($checkoutInfo['payment_type'] ?? 'full') === 'deposit')
                        <div class="total-row deposit">
                            <span class="label">Đặt cọc {{ $checkoutInfo['deposit_percent'] ?? 30 }}%</span>
                            <span class="value" style="color: #10b981; font-weight: 600;">{{ number_format($amountToPay, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="total-row remaining">
                            <span class="label">Còn lại (thanh toán sau)</span>
                            <span class="value">{{ number_format($remainingAmount, 0, ',', '.') }}đ</span>
                        </div>
                        @endif

                        <div class="total-row grand-total">
                            <span class="label">Thanh toán ngay</span>
                            <span class="value">{{ number_format($amountToPay, 0, ',', '.') }}đ</span>
                        </div>
                    </div>

                    <!-- VNPay Badge -->
                    <div class="vnpay-badge">
                        <i class="bi bi-credit-card-2-front"></i>
                        Thanh toán qua VNPay
                    </div>

                    <!-- Payment Note -->
                    <div class="payment-note">
                        <i class="bi bi-shield-check"></i>
                        <p>
                            Bạn sẽ được chuyển đến cổng thanh toán VNPay để hoàn tất giao dịch an toàn. 
                            Hỗ trợ ATM, Visa, MasterCard, QR Code.
                        </p>
                    </div>

                    <!-- Agreement -->
                    <div class="agreement-section">
                        <label class="agreement-check">
                            <input type="checkbox" id="agreeTerms">
                            <span>
                                Tôi đã đọc và đồng ý với 
                                <a href="#" onclick="return false;">Điều khoản sử dụng</a> 
                                và <a href="#" onclick="return false;">Chính sách hủy tour</a> 
                                của MountainBook.
                            </span>
                        </label>
                    </div>

                    <!-- Footer Actions -->
                    <div class="confirm-footer">
                        <button type="submit" class="btn-pay" id="btnPay" disabled>
                            <i class="bi bi-lock-fill"></i>
                            Thanh toán {{ number_format($amountToPay, 0, ',', '.') }}đ
                        </button>
                        <a href="{{ route('bookings.create', ['schedule' => $schedule->id]) }}" class="btn-back">
                            <i class="bi bi-arrow-left"></i>
                            Quay lại chỉnh sửa
                        </a>
                    </div>

                    <!-- Security -->
                    <div class="security-info">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Giao dịch được bảo mật SSL 256-bit</span>
                        <i class="bi bi-patch-check-fill"></i>
                        <span>Được bảo vệ bởi VNPay</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const agreeCheckbox = document.getElementById('agreeTerms');
    const btnPay = document.getElementById('btnPay');
    const confirmForm = document.getElementById('confirmForm');

    // Toggle button state based on agreement checkbox
    agreeCheckbox.addEventListener('change', function() {
        btnPay.disabled = !this.checked;
    });

    // Prevent double-submit
    confirmForm.addEventListener('submit', function(e) {
        if (!agreeCheckbox.checked) {
            e.preventDefault();
            alert('Vui lòng đồng ý với Điều khoản sử dụng trước khi tiếp tục.');
            return;
        }
        btnPay.disabled = true;
        btnPay.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang chuyển đến VNPay...';
    });
});
</script>
@endpush
@endsection
