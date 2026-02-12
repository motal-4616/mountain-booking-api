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
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 32px;
}

.checkout-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.checkout-step.active .step-number {
    background: #10b981;
    color: white;
    border: 2px solid #10b981;
}

.checkout-step.active .step-text {
    color: #10b981;
    font-weight: 600;
}

.step-number {
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

.step-text {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    transition: all 0.3s;
}

.checkout-step-divider {
    color: #cbd5e1;
    font-size: 20px;
    margin: 0 8px;
    padding-bottom: 20px;
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
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group textarea {
    resize: none;
    min-height: 80px;
}

.form-group input.is-invalid,
.form-group textarea.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    font-size: 13px;
    color: #ef4444;
    margin-top: 4px;
}

/* Quantity Control */
.quantity-control {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantity-control button {
    width: 40px;
    height: 40px;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.quantity-control button:hover {
    border-color: #10b981;
    background: #f0fdf4;
}

.quantity-control button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.quantity-control input {
    width: 80px;
    text-align: center;
    padding: 10px;
}

.quantity-info {
    font-size: 13px;
    color: #64748b;
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

.btn-checkout:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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

/* Alert */
.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.alert-danger strong {
    color: #7f1d1d;
}

.alert-danger ul {
    list-style: none;
    padding-left: 0;
}

.alert-danger li {
    padding: 4px 0;
}

/* Conflict Modal */
.conflict-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.conflict-modal.active {
    display: flex;
}

.conflict-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.conflict-modal-content {
    position: relative;
    background: white;
    border-radius: 16px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.conflict-modal-header {
    padding: 32px 32px 24px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
}

.conflict-modal-header i {
    font-size: 64px;
    color: #f59e0b;
    margin-bottom: 16px;
    display: block;
}

.conflict-modal-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.conflict-modal-body {
    padding: 24px 32px;
}

.conflict-modal-body p {
    font-size: 15px;
    line-height: 1.6;
    color: #475569;
    margin: 0;
}

.conflict-modal-footer {
    padding: 24px 32px 32px;
    display: flex;
    gap: 12px;
}

.conflict-modal-footer .btn-secondary,
.conflict-modal-footer .btn-primary {
    flex: 1;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.conflict-modal-footer .btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.conflict-modal-footer .btn-secondary:hover {
    background: #e2e8f0;
}

.conflict-modal-footer .btn-primary {
    background: #10b981;
    color: white;
}

.conflict-modal-footer .btn-primary:hover {
    background: #059669;
}

/* Coupon Section */
.coupon-section {
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.coupon-section h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #374151;
}

.coupon-input-group {
    display: flex;
    gap: 8px;
}

.coupon-input-group input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    text-transform: uppercase;
    transition: all 0.2s;
}

.coupon-input-group input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.coupon-input-group button {
    padding: 10px 16px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.coupon-input-group button:hover {
    background: #059669;
}

.coupon-message {
    display: none;
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 13px;
}

.coupon-message.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.coupon-message.error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.coupon-message i {
    margin-right: 6px;
}

.coupon-applied {
    display: none;
    margin-top: 10px;
}

.coupon-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #166534;
}

.coupon-tag i {
    color: #22c55e;
}

.remove-coupon {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-coupon:hover {
    background: #fee2e2;
}

.order-row.discount {
    color: #10b981;
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

    @if(session('conflict_warning'))
    <div id="conflictModal" class="conflict-modal active">
        <div class="conflict-modal-overlay" onclick="closeConflictModal()"></div>
        <div class="conflict-modal-content">
            <div class="conflict-modal-header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <h3>Lịch trình xung đột</h3>
            </div>
            <div class="conflict-modal-body">
                <p>{!! session('conflict_warning') !!}</p>
            </div>
            <div class="conflict-modal-footer">
                <a href="{{ route('tours.index') }}" class="btn-secondary">Xem tour khác</a>
                <a href="{{ route('bookings.index') }}" class="btn-primary">Xem tour đã đặt</a>
            </div>
        </div>
    </div>

    <script>
    function closeConflictModal() {
        document.getElementById('conflictModal').classList.remove('active');
    }
    </script>
    @endif

    <!-- Steps -->
    <div class="checkout-steps">
        <div class="checkout-step active">
            <span class="step-number">1</span>
            <span class="step-text">Thông tin đặt tour</span>
        </div>
        <div class="checkout-step-divider">
            <i class="bi bi-arrow-right"></i>
        </div>
        <div class="checkout-step">
            <span class="step-number">2</span>
            <span class="step-text">Hoàn tất</span>
        </div>
    </div>

    <form action="{{ route('bookings.store') }}" method="POST" id="checkoutForm">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
        
        <div class="checkout-layout">
            <!-- Left Column -->
            <div class="checkout-left">
                <!-- Customer Info Section -->
                <div class="checkout-section">
                    <div class="section-header">
                        <h2>Thông tin khách hàng</h2>
                    </div>
                    <div class="section-body">
                        <div class="form-row single">
                            <div class="form-group">
                                <label>Số lượng người</label>
                                <div class="quantity-control">
                                    <button type="button" id="decreaseQty"><i class="bi bi-dash"></i></button>
                                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $schedule->available_slots }}" readonly required>
                                    <button type="button" id="increaseQty"><i class="bi bi-plus"></i></button>
                                    <span class="quantity-info">Tối đa {{ $schedule->available_slots }} chỗ</span>
                                </div>
                                @error('quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và tên</label>
                                <input type="text" name="contact_name" value="{{ old('contact_name', auth()->check() ? auth()->user()->name : '') }}" class="@error('contact_name') is-invalid @enderror" required placeholder="Nguyễn Văn A">
                                @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone ?? '') }}" class="@error('contact_phone') is-invalid @enderror" required placeholder="0912345678">
                                @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row single">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', auth()->check() ? auth()->user()->email : '') }}" class="@error('contact_email') is-invalid @enderror" required placeholder="nguyen.van.a@gmail.com">
                                @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row single">
                            <div class="form-group">
                                <label>Ghi chú (tùy chọn)</label>
                                <textarea name="note" placeholder="Yêu cầu đặc biệt, dị ứng thực phẩm...">{{ old('note') }}</textarea>
                            </div>
                        </div>
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
                                    <p>Thanh toán toàn bộ <span id="fullAmountText">{{ number_format($schedule->price, 0, ',', '.') }}đ</span></p>
                                </div>
                                <span class="payment-type-badge">Khuyến nghị</span>
                            </label>

                            <!-- Deposit Payment -->
                            <label class="payment-type-option" data-type="deposit">
                                <input type="radio" name="payment_type" value="deposit">
                                <span class="payment-type-radio"></span>
                                <div class="payment-type-info">
                                    <h4>Đặt cọc trước 30%</h4>
                                    <p>Thanh toán <span id="depositAmountText">{{ number_format($schedule->price * 0.3, 0, ',', '.') }}đ</span>, phần còn lại thanh toán sau</p>
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
                                @if($tour->image)
                                <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}">
                                @else
                                <div style="width:100%;height:100%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                @endif
                            </div>
                            <div class="order-item-info">
                                <h4>{{ $tour->name }}</h4>
                                <p class="order-date">
                                    Ngày: {{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}
                                    @if($schedule->end_date)
                                    - {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="order-item-price">
                                <div class="price">{{ number_format($schedule->price, 0, ',', '.') }}đ</div>
                                <div class="quantity">x <span id="quantityDisplay">1</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="coupon-section">
                        <div class="coupon-input">
                            <input type="text" placeholder="Mã giảm giá" id="couponCodeInput">
                            <input type="hidden" name="coupon_code" id="couponCodeHidden">
                            <button type="button" id="applyCouponBtn">Áp dụng</button>
                        </div>
                        <div id="couponMessage" class="coupon-message" style="display:none;"></div>
                        <div id="couponApplied" class="coupon-applied" style="display:none;">
                            <span class="coupon-tag">
                                <i class="bi bi-tag-fill"></i>
                                <span id="appliedCouponCode"></span>
                                <button type="button" id="removeCouponBtn" class="remove-coupon">&times;</button>
                            </span>
                        </div>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span class="label">Tổng giá tour</span>
                            <span class="value" id="tourTotal">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="total-row discount-row" id="discountRow" style="display:none;">
                            <span class="label">Giảm giá</span>
                            <span class="value text-success" id="discountAmount">-0đ</span>
                        </div>
                        <div class="total-row" id="subtotalRow">
                            <span class="label">Tạm tính</span>
                            <span class="value" id="subtotal">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="total-row" id="depositRow" style="display: none;">
                            <span class="label">Đặt cọc 30%</span>
                            <span class="value text-primary" id="depositAmount">0đ</span>
                        </div>
                        <div class="total-row" id="remainingRow" style="display: none;">
                            <span class="label">Còn lại (thanh toán sau)</span>
                            <span class="value text-warning" id="remainingAmount">0đ</span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="label">Thanh toán ngay</span>
                            <span class="value" id="totalAmount">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                        </div>
                    </div>

                    <div class="checkout-footer">
                        <a href="{{ route('tours.show', $tour) }}" class="btn-back">
                            <i class="bi bi-arrow-left"></i>
                            Quay lại
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
    const pricePerPerson = {{ $schedule->price }};
    const maxSlots = {{ $schedule->available_slots }};
    
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const quantityDisplay = document.getElementById('quantityDisplay');
    const tourTotalEl = document.getElementById('tourTotal');
    const subtotalEl = document.getElementById('subtotal');
    const totalAmountEl = document.getElementById('totalAmount');
    const fullAmountText = document.getElementById('fullAmountText');
    const depositAmountText = document.getElementById('depositAmountText');
    const discountRow = document.getElementById('discountRow');
    const discountAmountEl = document.getElementById('discountAmount');
    const depositRow = document.getElementById('depositRow');
    const remainingRow = document.getElementById('remainingRow');
    const depositAmountEl = document.getElementById('depositAmount');
    const remainingAmountEl = document.getElementById('remainingAmount');
    
    const paymentOptions = document.querySelectorAll('.payment-type-option');
    
    // Coupon elements
    const couponCodeInput = document.getElementById('couponCodeInput');
    const couponCodeHidden = document.getElementById('couponCodeHidden');
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    const couponMessage = document.getElementById('couponMessage');
    const couponApplied = document.getElementById('couponApplied');
    const appliedCouponCode = document.getElementById('appliedCouponCode');
    const removeCouponBtn = document.getElementById('removeCouponBtn');
    
    // Coupon state
    let appliedCoupon = null;
    let discountValue = 0;
    
    function updateTotals() {
        const quantity = parseInt(quantityInput.value);
        const tourTotal = pricePerPerson * quantity;
        
        // Recalculate discount if coupon is applied
        if (appliedCoupon) {
            validateCoupon(couponCodeHidden.value, tourTotal, true);
        }
        
        const finalPrice = tourTotal - discountValue;
        const deposit = Math.round(finalPrice * 0.3);
        const remaining = finalPrice - deposit;
        
        quantityDisplay.textContent = quantity;
        tourTotalEl.textContent = formatCurrency(tourTotal);
        subtotalEl.textContent = formatCurrency(finalPrice);
        fullAmountText.textContent = formatCurrency(finalPrice);
        depositAmountText.textContent = formatCurrency(deposit);
        
        // Update discount display
        if (discountValue > 0) {
            discountRow.style.display = 'flex';
            discountAmountEl.textContent = '-' + formatCurrency(discountValue);
        } else {
            discountRow.style.display = 'none';
        }
        
        // Update total based on payment type
        const selectedType = document.querySelector('.payment-type-option.selected').dataset.type;
        if (selectedType === 'full') {
            totalAmountEl.textContent = formatCurrency(finalPrice);
            depositRow.style.display = 'none';
            remainingRow.style.display = 'none';
        } else {
            totalAmountEl.textContent = formatCurrency(deposit);
            depositAmountEl.textContent = formatCurrency(deposit);
            remainingAmountEl.textContent = formatCurrency(remaining);
            depositRow.style.display = 'flex';
            remainingRow.style.display = 'flex';
        }
        
        // Update button states
        decreaseBtn.disabled = quantity <= 1;
        increaseBtn.disabled = quantity >= maxSlots;
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    }
    
    function validateCoupon(code, totalAmount, silent = false) {
        if (!code) return;
        
        fetch('{{ route("coupon.apply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                code: code,
                total_amount: totalAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appliedCoupon = data.data;
                discountValue = data.data.discount_amount;
                couponCodeHidden.value = data.data.coupon_code;
                
                if (!silent) {
                    showCouponSuccess(data.message, data.data.coupon_code);
                }
                updateTotals();
            } else {
                if (!silent) {
                    showCouponError(data.message);
                }
            }
        })
        .catch(error => {
            if (!silent) {
                showCouponError('Có lỗi xảy ra, vui lòng thử lại.');
            }
        });
    }
    
    function showCouponSuccess(message, code) {
        couponMessage.style.display = 'block';
        couponMessage.className = 'coupon-message success';
        couponMessage.innerHTML = '<i class="bi bi-check-circle"></i> ' + message;
        
        couponApplied.style.display = 'block';
        appliedCouponCode.textContent = code;
        couponCodeInput.style.display = 'none';
        applyCouponBtn.style.display = 'none';
        
        setTimeout(() => {
            couponMessage.style.display = 'none';
        }, 3000);
    }
    
    function showCouponError(message) {
        couponMessage.style.display = 'block';
        couponMessage.className = 'coupon-message error';
        couponMessage.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + message;
        
        setTimeout(() => {
            couponMessage.style.display = 'none';
        }, 3000);
    }
    
    function removeCoupon() {
        appliedCoupon = null;
        discountValue = 0;
        couponCodeHidden.value = '';
        couponCodeInput.value = '';
        couponCodeInput.style.display = 'block';
        applyCouponBtn.style.display = 'block';
        couponApplied.style.display = 'none';
        discountRow.style.display = 'none';
        updateTotals();
    }
    
    // Coupon event listeners
    applyCouponBtn.addEventListener('click', function() {
        const code = couponCodeInput.value.trim();
        if (code) {
            const quantity = parseInt(quantityInput.value);
            const subtotal = pricePerPerson * quantity;
            validateCoupon(code, subtotal);
        }
    });
    
    couponCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyCouponBtn.click();
        }
    });
    
    removeCouponBtn.addEventListener('click', removeCoupon);
    
    // Quantity controls
    decreaseBtn.addEventListener('click', function() {
        const current = parseInt(quantityInput.value);
        if (current > 1) {
            quantityInput.value = current - 1;
            updateTotals();
        }
    });
    
    increaseBtn.addEventListener('click', function() {
        const current = parseInt(quantityInput.value);
        if (current < maxSlots) {
            quantityInput.value = current + 1;
            updateTotals();
        }
    });
    
    // Payment type selection
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
            updateTotals();
        });
    });
    
    // Initial update
    updateTotals();
});
</script>
@endpush
@endsection
