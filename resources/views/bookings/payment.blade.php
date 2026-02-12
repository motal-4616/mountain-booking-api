@extends('layouts.app')

@section('title', 'Thanh toán an toàn')

@push('styles')
<style>
/* Main Layout */
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.checkout-header {
    margin-bottom: 32px;
}

.checkout-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.checkout-header p {
    color: #64748b;
    margin: 0;
}

/* Steps */
.checkout-steps {
    display: flex;
    gap: 24px;
    margin-bottom: 32px;
}

.checkout-step {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #94a3b8;
}

.checkout-step.active {
    color: #10b981;
    font-weight: 600;
}

.checkout-step.completed {
    color: #10b981;
}

.step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    background: #e2e8f0;
    color: #64748b;
}

.checkout-step.active .step-number,
.checkout-step.completed .step-number {
    background: #10b981;
    color: white;
}

/* Two Column Layout */
.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 40px;
    align-items: start;
}

/* Left Column */
.checkout-left {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Section Card */
.checkout-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #1a1a1a;
}

.section-body {
    padding: 24px;
}

/* Form Inputs */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.form-row.single {
    grid-template-columns: 1fr;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #f9fafb;
    color: #374151;
}

.form-group textarea {
    resize: none;
    min-height: 80px;
}

/* Payment Type Selection */
.payment-type-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.payment-type-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.payment-type-option:hover {
    border-color: #10b981;
    background: #f0fdf4;
}

.payment-type-option.selected {
    border-color: #10b981;
    background: #f0fdf4;
}

.payment-type-option input[type="radio"] {
    display: none;
}

.payment-type-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.payment-type-option.selected .payment-type-radio {
    border-color: #10b981;
}

.payment-type-option.selected .payment-type-radio::after {
    content: '';
    width: 10px;
    height: 10px;
    background: #10b981;
    border-radius: 50%;
}

.payment-type-info {
    flex: 1;
}

.payment-type-info h4 {
    font-size: 15px;
    font-weight: 600;
    margin: 0 0 2px 0;
    color: #1a1a1a;
}

.payment-type-info p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.payment-type-badge {
    padding: 4px 10px;
    background: #10b981;
    color: white;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
}

/* Payment Method */
.payment-method-vnpay {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #f8fafc;
    border: 2px solid #10b981;
    border-radius: 10px;
}

.vnpay-logo {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #0066b3 0%, #004494 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
}

.vnpay-info h4 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: #1a1a1a;
}

.vnpay-info p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

/* Right Column - Order Summary */
.checkout-right {
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

.order-items {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.order-item {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.order-item:last-child {
    margin-bottom: 0;
}

.order-item-image {
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-info {
    flex: 1;
    min-width: 0;
}

.order-item-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: #1a1a1a;
}

.order-item-info .order-date {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.order-item-price {
    text-align: right;
    flex-shrink: 0;
}

.order-item-price .price {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}

.order-item-price .quantity {
    font-size: 12px;
    color: #64748b;
}

/* Coupon */
.coupon-section {
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.coupon-input {
    display: flex;
    gap: 12px;
}

.coupon-input input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
}

.coupon-input button {
    padding: 10px 20px;
    background: #1a1a1a;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.coupon-input button:hover {
    background: #374151;
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

.total-row.discount .value {
    color: #ef4444;
}

.total-row.grand-total {
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
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

/* Footer Actions */
.checkout-footer {
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

.btn-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px 24px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-checkout:hover {
    background: #059669;
}

.btn-checkout i {
    font-size: 18px;
}

/* Payment Info Note */
.payment-note {
    padding: 16px 20px;
    background: #eff6ff;
    border-radius: 8px;
    display: flex;
    gap: 12px;
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

/* Responsive */
@media (max-width: 992px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .checkout-right {
        position: static;
    }
}

@media (max-width: 576px) {
    .checkout-container {
        padding: 20px 16px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .checkout-steps {
        flex-wrap: wrap;
        gap: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="checkout-container">
    <!-- Header -->
    <div class="checkout-header">
        <h1>Thanh toán an toàn</h1>
        <p>Vui lòng kiểm tra thông tin đơn hàng và hoàn tất các bước thanh toán</p>
    </div>

    <!-- Steps -->
    <div class="checkout-steps">
        <div class="checkout-step completed">
            <span class="step-number"><i class="bi bi-check"></i></span>
            <span>1. Thông tin</span>
        </div>
        <div class="checkout-step active">
            <span class="step-number">2</span>
            <span>2. Thanh toán</span>
        </div>
        <div class="checkout-step">
            <span class="step-number">3</span>
            <span>3. Xác nhận</span>
        </div>
    </div>

    <form action="{{ route('bookings.payment.vnpay', $booking->id) }}" method="POST" id="paymentForm">
        @csrf
        
        <div class="checkout-layout">
            <!-- Left Column -->
            <div class="checkout-left">
                <!-- Customer Info Section -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h2>Thông tin khách hàng</h2>
                    </div>
                    <div class="section-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và tên</label>
                                <input type="text" value="{{ $booking->contact_name }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" value="{{ $booking->contact_phone }}" readonly>
                            </div>
                        </div>
                        <div class="form-row single">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="{{ $booking->contact_email }}" readonly>
                            </div>
                        </div>
                        @if($booking->note)
                        <div class="form-row single">
                            <div class="form-group">
                                <label>Ghi chú</label>
                                <textarea readonly>{{ $booking->note }}</textarea>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Type Section -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h2>Hình thức thanh toán</h2>
                    </div>
                    <div class="section-body">
                        <div class="payment-type-options">
                            <!-- Full Payment -->
                            <label class="payment-type-option selected" data-type="full">
                                <input type="radio" name="payment_type" value="full" checked>
                                <span class="payment-type-radio"></span>
                                <div class="payment-type-info">
                                    <h4>Thanh toán 100%</h4>
                                    <p>Thanh toán toàn bộ {{ number_format($booking->total_amount, 0, ',', '.') }}đ</p>
                                </div>
                                <span class="payment-type-badge">Khuyến nghị</span>
                            </label>

                            <!-- Deposit Payment -->
                            <label class="payment-type-option" data-type="deposit">
                                <input type="radio" name="payment_type" value="deposit">
                                <span class="payment-type-radio"></span>
                                <div class="payment-type-info">
                                    <h4>Đặt cọc trước 30%</h4>
                                    <p>Thanh toán {{ number_format($booking->total_amount * 0.3, 0, ',', '.') }}đ, phần còn lại thanh toán sau</p>
                                </div>
                            </label>
                        </div>

                        <div class="payment-note">
                            <i class="bi bi-info-circle"></i>
                            <p>
                                <strong>Lưu ý:</strong> Nếu chọn đặt cọc, bạn sẽ thanh toán phần còn lại tại điểm tập kết trước khi khởi hành tour.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h2>Phương thức thanh toán</h2>
                    </div>
                    <div class="section-body">
                        <div class="payment-method-vnpay">
                            <div class="vnpay-logo">
                                <i class="bi bi-credit-card-2-front" style="font-size: 24px;"></i>
                            </div>
                            <div class="vnpay-info">
                                <h4>VNPay</h4>
                                <p>Thanh toán qua cổng VNPay - ATM/Visa/MasterCard/QR Code</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="checkout-right">
                <div class="order-summary">
                    <div class="order-header">
                        <h3>Đơn hàng của bạn</h3>
                    </div>
                    
                    <div class="order-items">
                        <div class="order-item">
                            <div class="order-item-image">
                                @if($booking->schedule->tour->image)
                                <img src="{{ asset($booking->schedule->tour->image) }}" alt="{{ $booking->schedule->tour->name }}">
                                @else
                                <div style="width:100%;height:100%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                @endif
                            </div>
                            <div class="order-item-info">
                                <h4>{{ $booking->schedule->tour->name }}</h4>
                                <p class="order-date">
                                    Ngày: {{ \Carbon\Carbon::parse($booking->schedule->start_date)->format('d/m/Y') }}
                                    @if($booking->schedule->end_date)
                                    - {{ \Carbon\Carbon::parse($booking->schedule->end_date)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="order-item-price">
                                <div class="price">{{ number_format($booking->schedule->price, 0, ',', '.') }}đ</div>
                                <div class="quantity">x {{ $booking->quantity }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="coupon-section">
                        <div class="coupon-input">
                            <input type="text" placeholder="Mã giảm giá" name="coupon_code">
                            <button type="button">Áp dụng</button>
                        </div>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span class="label">Tổng giá tour</span>
                            <span class="value" id="tourTotalAmount">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="total-row" id="depositRow" style="display: none;">
                            <span class="label">Đặt cọc 30%</span>
                            <span class="value text-primary" id="depositAmount">0đ</span>
                        </div>
                        <div class="total-row" id="remainingRow" style="display: none;">
                            <span class="label">Còn lại (thanh toán sau)</span>
                            <span class="value text-warning" id="remainingAmount">0đ</span>
                        </div>
                        <div class="total-row">
                            <span class="label">Giảm giá</span>
                            <span class="value">0đ</span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="label">Thanh toán ngay</span>
                            <span class="value" id="totalAmount">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</span>
                        </div>
                    </div>

                    <div class="checkout-footer">
                        <a href="{{ route('bookings.index') }}" class="btn-back">
                            <i class="bi bi-arrow-left"></i>
                            Quay lại giỏ hàng
                        </a>
                        <button type="submit" class="btn-checkout">
                            Tiếp tục thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-type-option');
    const totalAmountEl = document.getElementById('totalAmount');
    const depositRow = document.getElementById('depositRow');
    const remainingRow = document.getElementById('remainingRow');
    const depositAmountEl = document.getElementById('depositAmount');
    const remainingAmountEl = document.getElementById('remainingAmount');
    const fullAmount = {{ $booking->total_amount }};
    const depositAmount = Math.round(fullAmount * 0.3);
    const remainingAmount = fullAmount - depositAmount;
    
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected from all
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            // Add selected to clicked
            this.classList.add('selected');
            // Check the radio
            this.querySelector('input[type="radio"]').checked = true;
            
            // Update total amount and display
            const type = this.dataset.type;
            if (type === 'full') {
                totalAmountEl.textContent = new Intl.NumberFormat('vi-VN').format(fullAmount) + 'đ';
                depositRow.style.display = 'none';
                remainingRow.style.display = 'none';
            } else {
                totalAmountEl.textContent = new Intl.NumberFormat('vi-VN').format(depositAmount) + 'đ';
                depositAmountEl.textContent = new Intl.NumberFormat('vi-VN').format(depositAmount) + 'đ';
                remainingAmountEl.textContent = new Intl.NumberFormat('vi-VN').format(remainingAmount) + 'đ';
                depositRow.style.display = 'flex';
                remainingRow.style.display = 'flex';
            }
        });
    });
});
</script>
@endpush
@endsection
