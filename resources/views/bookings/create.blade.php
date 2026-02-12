@extends('layouts.app')

@section('title', 'Đặt vé - ' . $tour->name)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tours.show', $tour) }}">{{ $tour->name }}</a></li>
            <li class="breadcrumb-item active">Đặt vé</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Form đặt vé -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Thông tin đặt vé</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('bookings.store') }}">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

                        <!-- Số lượng người -->
                        <div class="mb-4">
                            <label for="quantity" class="form-label">Số lượng người <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <input type="number"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity"
                                       name="quantity"
                                       value="{{ old('quantity', 1) }}"
                                       min="1"
                                       max="{{ $schedule->available_slots }}"
                                       required>
                                <span class="input-group-text">người (tối đa {{ $schedule->available_slots }})</span>
                            </div>
                            @error('quantity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Thông tin liên hệ</h6>

                        <!-- Tên liên hệ -->
                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text"
                                       class="form-control @error('contact_name') is-invalid @enderror"
                                       id="contact_name"
                                       name="contact_name"
                                       value="{{ old('contact_name', auth()->user()->name) }}"
                                       required>
                            </div>
                            @error('contact_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Số điện thoại -->
                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text"
                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                       id="contact_phone"
                                       name="contact_phone"
                                       value="{{ old('contact_phone', auth()->user()->phone) }}"
                                       placeholder="0912 345 678"
                                       required>
                            </div>
                            @error('contact_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email"
                                       class="form-control @error('contact_email') is-invalid @enderror"
                                       id="contact_email"
                                       name="contact_email"
                                       value="{{ old('contact_email', auth()->user()->email) }}"
                                       required>
                            </div>
                            @error('contact_email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-4">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note"
                                      name="note"
                                      rows="3"
                                      placeholder="Yêu cầu đặc biệt, dị ứng, sức khỏe...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hình thức thanh toán -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3"><i class="bi bi-credit-card me-2"></i>Hình thức thanh toán</h6>
                                
                                <!-- Thanh toán đầy đủ -->
                                <div class="form-check mb-3 p-3 border rounded" id="payment-full-option">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="payment_type" 
                                           id="payment_full" 
                                           value="full" 
                                           {{ old('payment_type', 'full') == 'full' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="payment_full">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Thanh toán toàn bộ (100%)</strong>
                                                <div class="text-muted small">Thanh toán đủ ngay</div>
                                            </div>
                                            <span class="badge bg-success">Khuyến nghị</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- Thanh toán đặt cọc -->
                                <div class="form-check mb-2 p-3 border rounded" id="payment-deposit-option">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="payment_type" 
                                           id="payment_deposit" 
                                           value="deposit"
                                           {{ old('payment_type') == 'deposit' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="payment_deposit">
                                        <div>
                                            <strong>Đặt cọc trước</strong>
                                            <div class="text-muted small">Thanh toán phần còn lại tại điểm tập kết</div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Chọn % đặt cọc -->
                                <div id="deposit-percent-section" style="display: {{ old('payment_type') == 'deposit' ? 'block' : 'none' }};" class="ms-4 mt-2">
                                    <label class="form-label small">Chọn mức đặt cọc:</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="deposit_percent" 
                                               id="deposit_30" 
                                               value="30"
                                               {{ old('deposit_percent', '30') == '30' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="deposit_30">
                                            <strong>Đặt cọc 30%</strong>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-arrow-right me-1"></i>Thanh toán trước: <strong class="text-primary" id="deposit-30-amount">0đ</strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-arrow-right me-1"></i>Còn lại thanh toán sau: <strong class="text-warning" id="remaining-30-amount">0đ</strong>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="deposit_percent" 
                                               id="deposit_50" 
                                               value="50"
                                               {{ old('deposit_percent') == '50' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="deposit_50">
                                            <strong>Đặt cọc 50%</strong>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-arrow-right me-1"></i>Thanh toán trước: <strong class="text-primary" id="deposit-50-amount">0đ</strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-arrow-right me-1"></i>Còn lại thanh toán sau: <strong class="text-warning" id="remaining-50-amount">0đ</strong>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                @error('payment_type')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                @error('deposit_percent')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror

                                <div class="alert alert-info small mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Lưu ý:</strong> Đây là chế độ TEST/DEMO, không có giao dịch thật.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100" data-loading-text="Đang xử lý đặt vé...">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận đặt vé
                        </button>
                    
                        <script>
                            // Xử lý hiển thị section chọn % đặt cọc
                            document.addEventListener('DOMContentLoaded', function() {
                                const paymentFull = document.getElementById('payment_full');
                                const paymentDeposit = document.getElementById('payment_deposit');
                                const depositSection = document.getElementById('deposit-percent-section');
                                const quantityInput = document.getElementById('quantity');
                                const pricePerPerson = {{ $schedule->price_per_person }};
                                
                                function toggleDepositSection() {
                                    if (paymentDeposit.checked) {
                                        depositSection.style.display = 'block';
                                    } else {
                                        depositSection.style.display = 'none';
                                    }
                                }
                                
                                function updateDepositAmounts() {
                                    const quantity = parseInt(quantityInput.value) || 1;
                                    const totalPrice = pricePerPerson * quantity;
                                    
                                    // Tính 30%
                                    const deposit30 = Math.round(totalPrice * 0.3);
                                    const remaining30 = totalPrice - deposit30;
                                    document.getElementById('deposit-30-amount').textContent = 
                                        new Intl.NumberFormat('vi-VN').format(deposit30) + 'đ';
                                    document.getElementById('remaining-30-amount').textContent = 
                                        new Intl.NumberFormat('vi-VN').format(remaining30) + 'đ';
                                    
                                    // Tính 50%
                                    const deposit50 = Math.round(totalPrice * 0.5);
                                    const remaining50 = totalPrice - deposit50;
                                    document.getElementById('deposit-50-amount').textContent = 
                                        new Intl.NumberFormat('vi-VN').format(deposit50) + 'đ';
                                    document.getElementById('remaining-50-amount').textContent = 
                                        new Intl.NumberFormat('vi-VN').format(remaining50) + 'đ';
                                }
                                
                                // Lắng nghe sự kiện
                                paymentFull.addEventListener('change', toggleDepositSection);
                                paymentDeposit.addEventListener('change', toggleDepositSection);
                                quantityInput.addEventListener('input', updateDepositAmounts);
                                
                                // Cập nhật lần đầu
                                updateDepositAmounts();
                            });
                        </script>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thông tin tour -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin tour</h5>
                </div>
                <div class="card-body">
                    @if($tour->image)
                        <img src="{{ asset($tour->image) }}" class="img-fluid rounded mb-3" alt="{{ $tour->name }}">
                    @endif

                    <h5>{{ $tour->name }}</h5>

                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt text-muted me-2"></i>{{ $tour->location }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar-event text-muted me-2"></i>{{ $schedule->formatted_date }}
                            @if($schedule->end_date)
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                {{ $schedule->formatted_end_date }}
                            @endif
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-clock text-muted me-2"></i>{{ $schedule->duration_text }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-speedometer2 text-muted me-2"></i>Độ khó: {{ $tour->difficulty_text }}
                        </li>
                    </ul>

                    <hr>

                    <h6 class="mb-3">Chi tiết thanh toán</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Giá vé (1 người):</span>
                        <span class="fw-bold">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Số lượng:</span>
                        <span class="fw-bold" id="summary-quantity">1</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng giá tour:</span>
                        <span class="fw-bold" id="summary-total">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2" id="summary-deposit-row" style="display: none !important;">
                        <span class="text-muted">Đặt cọc 30%:</span>
                        <span class="fw-bold text-primary" id="summary-deposit">0đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2" id="summary-remaining-row" style="display: none !important;">
                        <span class="text-muted">Còn lại (thanh toán sau):</span>
                        <span class="fw-bold text-warning" id="summary-remaining">0đ</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h6 mb-0">Thanh toán ngay:</span>
                        <span class="h5 text-success mb-0" id="summary-payment-now">{{ number_format($schedule->price, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Số tiền sẽ tự động cập nhật khi bạn thay đổi số lượng hoặc hình thức thanh toán.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Cập nhật tóm tắt bên phải khi thay đổi
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const paymentFull = document.getElementById('payment_full');
        const paymentDeposit = document.getElementById('payment_deposit');
        const deposit30 = document.getElementById('deposit_30');
        const deposit50 = document.getElementById('deposit_50');
        const pricePerPerson = {{ $schedule->price }};
        
        // Elements trong phần tóm tắt
        const summaryQuantity = document.getElementById('summary-quantity');
        const summaryTotal = document.getElementById('summary-total');
        const summaryDeposit = document.getElementById('summary-deposit');
        const summaryRemaining = document.getElementById('summary-remaining');
        const summaryPaymentNow = document.getElementById('summary-payment-now');
        const summaryDepositRow = document.getElementById('summary-deposit-row');
        const summaryRemainingRow = document.getElementById('summary-remaining-row');
        
        function updateSummary() {
            const quantity = parseInt(quantityInput.value) || 1;
            const totalPrice = pricePerPerson * quantity;
            
            // Cập nhật số lượng và tổng
            summaryQuantity.textContent = quantity + ' người';
            summaryTotal.textContent = new Intl.NumberFormat('vi-VN').format(totalPrice) + 'đ';
            
            // Kiểm tra loại thanh toán
            if (paymentDeposit.checked) {
                // Lấy % đặt cọc
                let depositPercent = 0.3; // mặc định 30%
                if (deposit50 && deposit50.checked) {
                    depositPercent = 0.5;
                }
                
                const depositAmount = Math.round(totalPrice * depositPercent);
                const remainingAmount = totalPrice - depositAmount;
                
                // Hiển thị các dòng đặt cọc
                summaryDeposit.textContent = new Intl.NumberFormat('vi-VN').format(depositAmount) + 'đ';
                summaryRemaining.textContent = new Intl.NumberFormat('vi-VN').format(remainingAmount) + 'đ';
                summaryDepositRow.style.display = 'flex';
                summaryRemainingRow.style.display = 'flex';
                summaryPaymentNow.textContent = new Intl.NumberFormat('vi-VN').format(depositAmount) + 'đ';
            } else {
                // Thanh toán đầy đủ - ẩn các dòng đặt cọc
                summaryDepositRow.style.display = 'none';
                summaryRemainingRow.style.display = 'none';
                summaryPaymentNow.textContent = new Intl.NumberFormat('vi-VN').format(totalPrice) + 'đ';
            }
        }
        
        // Lắng nghe các sự kiện
        quantityInput.addEventListener('input', updateSummary);
        paymentFull.addEventListener('change', updateSummary);
        paymentDeposit.addEventListener('change', updateSummary);
        if (deposit30) deposit30.addEventListener('change', updateSummary);
        if (deposit50) deposit50.addEventListener('change', updateSummary);
        
        // Cập nhật lần đầu
        updateSummary();
    });
</script>
@endpush
@endsection
