@extends('layouts.app')

@section('title', 'Chi tiết đơn #' . $booking->id)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Vé của tôi</a></li>
            <li class="breadcrumb-item active">Đơn #{{ $booking->id }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Thông tin đơn -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn đặt vé #{{ $booking->id }}</h5>
                    <span class="badge bg-{{ $booking->status_badge }} fs-6">{{ $booking->status_text }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tour</label>
                            <p class="mb-0 fw-bold">{{ $booking->schedule->tour->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Ngày khởi hành</label>
                            <p class="mb-0 fw-bold">{{ $booking->schedule->formatted_date }}</p>
                        </div>
                        @if(!in_array($booking->status, ['cancelled', 'refund_processing', 'refunded']) && $booking->schedule->days_until_departure >= 0)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Thời gian còn lại</label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $booking->schedule->days_until_departure <= 7 ? 'warning' : 'info' }} fs-6 px-3 py-2">
                                        <i class="bi bi-hourglass-split me-2"></i>{{ $booking->schedule->time_until_departure }}
                                    </span>
                                </p>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Số lượng người</label>
                            <p class="mb-0">{{ $booking->quantity }} người</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tổng tiền</label>
                            <p class="mb-0 text-success fw-bold fs-5">{{ $booking->formatted_price }}</p>
                        </div>
                        @if($booking->discount_amount > 0)
                            @if($booking->coupon_code)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Mã giảm giá</label>
                                <p class="mb-0">
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $booking->coupon_code }}
                                    </span>
                                </p>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Số tiền giảm</label>
                                <p class="mb-0 text-danger fw-bold">
                                    -{{ number_format($booking->discount_amount, 0, ',', '.') }}đ
                                </p>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phương thức thanh toán</label>
                            <p class="mb-0">
                                @if($booking->payment_method)
                                    @if($booking->payment_method === 'vnpay')
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-wallet2 me-1"></i>VNPay
                                        </span>
                                    @elseif($booking->payment_method === 'cash')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-cash-coin me-1"></i>Tiền mặt
                                        </span>
                                    @elseif($booking->payment_method === 'transfer')
                                        <span class="badge bg-info px-3 py-2">
                                            <i class="bi bi-bank me-1"></i>Chuyển khoản
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-clock me-1"></i>Chưa thanh toán
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Trạng thái thanh toán</label>
                            <p class="mb-0">
                                @switch($booking->payment_status)
                                    @case('paid')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã thanh toán</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning"><i class="bi bi-hourglass-split me-1"></i>Đã đặt cọc</span>
                                        @break
                                    @case('refunded')
                                        <span class="badge bg-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Đã hoàn tiền</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Chưa thanh toán</span>
                                @endswitch
                            </p>
                        </div>
                        @if($booking->paid_amount > 0)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Số tiền đã thanh toán</label>
                            <p class="mb-0 text-primary fw-bold">{{ number_format($booking->paid_amount, 0, ',', '.') }}đ</p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Ngày đặt</label>
                            <p class="mb-0">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Địa điểm</label>
                            <p class="mb-0">{{ $booking->schedule->tour->location }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin liên hệ -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Thông tin liên hệ</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Họ tên</label>
                            <p class="mb-0">{{ $booking->contact_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Số điện thoại</label>
                            <p class="mb-0">{{ $booking->contact_phone }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0">{{ $booking->contact_email }}</p>
                        </div>
                        @if($booking->note)
                            <div class="col-12">
                                <label class="text-muted small">Ghi chú</label>
                                <p class="mb-0">{{ $booking->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Đánh giá tour -->
            @php
                $endDate = $booking->schedule->end_date ?? $booking->schedule->departure_date;
                $tourEnded = \Carbon\Carbon::parse($endDate)->lt(now());
                $hasPaid = in_array($booking->payment_status, ['paid', 'partial']);
                $existingReview = \App\Models\Review::where('user_id', Auth::id())
                    ->where('booking_id', $booking->id)
                    ->first();
            @endphp
            
            @if($hasPaid)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-star me-2"></i>Đánh giá tour</h5>
                </div>
                <div class="card-body">
                    @if($tourEnded)
                        @if($existingReview)
                            <!-- Đã đánh giá -->
                            <div class="review-submitted">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="review-icon">
                                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                    </div>
                                    <div style="flex-grow: 1;">
                                        <h6 class="mb-2">Bạn đã đánh giá tour này</h6>
                                        <div class="mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $existingReview->rating)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @else
                                                    <i class="bi bi-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2 text-muted">({{ $existingReview->rating }}/5)</span>
                                        </div>
                                        @if($existingReview->title)
                                            <p class="fw-bold mb-1">{{ $existingReview->title }}</p>
                                        @endif
                                        @if($existingReview->comment)
                                            <p class="text-muted mb-2">{{ $existingReview->comment }}</p>
                                        @endif
                                        <small class="text-muted">
                                            @switch($existingReview->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Đang chờ duyệt</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Đã hiển thị</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Bị từ chối</span>
                                                    @if($existingReview->admin_note)
                                                        <p class="text-danger small mt-1 mb-0">Lý do: {{ $existingReview->admin_note }}</p>
                                                    @endif
                                                    @break
                                            @endswitch
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Chưa đánh giá -->
                            <div class="review-prompt">
                                <div class="text-center py-3">
                                    <i class="bi bi-chat-heart text-primary fs-1 mb-3 d-block"></i>
                                    <h6 class="fw-bold mb-2">Chia sẻ trải nghiệm của bạn!</h6>
                                    <p class="text-muted mb-3">
                                        Tour đã kết thúc. Hãy để lại đánh giá để giúp những du khách khác có thêm thông tin.
                                    </p>
                                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                        <i class="bi bi-star me-2"></i>Viết đánh giá
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Tour chưa kết thúc -->
                        <div class="review-waiting text-center py-3">
                            <i class="bi bi-clock text-muted fs-1 mb-3 d-block"></i>
                            <h6 class="fw-bold mb-2">Tour chưa kết thúc</h6>
                            <p class="text-muted mb-0">
                                Bạn có thể đánh giá sau khi hoàn thành tour vào ngày 
                                <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>

                    @if($booking->status === 'pending' && $booking->schedule->days_until_departure > 0)
                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST" id="cancelForm">
                            @csrf
                            @method('PATCH')
                            <button type="button" class="btn btn-danger w-100" id="btnCancelBooking">
                                <i class="bi bi-x-circle me-2"></i>Hủy đơn
                            </button>
                        </form>
                    @elseif($booking->status === 'confirmed' && $booking->schedule->days_until_departure > 0)
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                            <i class="bi bi-x-circle me-2"></i>Yêu cầu hủy đơn
                        </button>
                        <div class="alert alert-warning small mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Đơn đã xác nhận. Bạn cần nhập lý do hủy và chờ xử lý hoàn tiền.
                        </div>
                    @elseif($booking->status === 'refund_processing')
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-hourglass-split me-1"></i>
                            Đơn hàng đang được xử lý hoàn tiền. Vui lòng chờ.
                        </div>
                    @elseif($booking->status === 'refunded')
                        <div class="alert alert-success small mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Đơn đã được hoàn tiền thành công.
                        </div>
                    @elseif(in_array($booking->status, ['confirmed', 'pending']) && $booking->schedule->days_until_departure <= 0)
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Tour đã khởi hành. Không thể hủy đơn.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thông tin hủy đơn (nếu đã bị hủy hoặc đang hoàn tiền) -->
            @if(in_array($booking->status, ['cancelled', 'refund_processing', 'refunded']))
            <div class="card shadow-sm border-0 mb-4 {{ $booking->status === 'refund_processing' ? 'border-warning' : 'border-danger' }}">
                <div class="card-header {{ $booking->status === 'refund_processing' ? 'bg-warning' : 'bg-danger' }} text-white">
                    <h6 class="mb-0">
                        @if($booking->status === 'refund_processing')
                            <i class="bi bi-hourglass-split me-2"></i>Đang xử lý hoàn tiền
                        @else
                            <i class="bi bi-x-circle me-2"></i>Thông tin hủy đơn
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if($booking->status === 'refund_processing')
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Đơn hàng đang được xử lý hoàn tiền qua VNPay. Vui lòng chờ trong giây lát.
                    </div>
                    @endif

                    @if($booking->cancellation_reason)
                    <div class="mb-3">
                        <label class="text-muted small">Lý do hủy</label>
                        <p class="mb-0">{{ $booking->cancellation_reason }}</p>
                    </div>
                    @endif
                    @if($booking->cancelled_at)
                    <div class="mb-3">
                        <label class="text-muted small">Thời gian hủy</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($booking->cancelled_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($booking->cancelledByUser)
                    <div class="mb-3">
                        <label class="text-muted small">Người hủy</label>
                        <p class="mb-0">
                            @if($booking->cancelledByUser->isAdmin())
                                <span class="badge bg-primary">Quản trị viên</span>
                                {{ $booking->cancelledByUser->name }}
                            @else
                                <span class="badge bg-secondary">Khách hàng</span>
                                {{ $booking->cancelledByUser->name }}
                            @endif
                        </p>
                    </div>
                    @endif

                    {{-- Thông tin hoàn tiền VNPay --}}
                    @if($booking->refund_status)
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-wallet2 me-2"></i>Thông tin hoàn tiền VNPay</h6>
                    <div class="mb-2">
                        <label class="text-muted small">Trạng thái hoàn tiền</label>
                        <p class="mb-0">
                            @switch($booking->refund_status)
                                @case('processing')
                                    <span class="badge bg-warning"><i class="bi bi-hourglass-split me-1"></i>Đang xử lý</span>
                                    @break
                                @case('success')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hoàn tiền thành công</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Hoàn tiền thất bại</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                    @if($booking->refund_message)
                    <div class="mb-2">
                        <label class="text-muted small">Kết quả từ VNPay</label>
                        <p class="mb-0">{{ $booking->refund_message }}</p>
                    </div>
                    @endif
                    @if($booking->refund_transaction_ref)
                    <div class="mb-2">
                        <label class="text-muted small">Mã giao dịch hoàn tiền</label>
                        <p class="mb-0"><code>{{ $booking->refund_transaction_ref }}</code></p>
                    </div>
                    @endif
                    @if($booking->refund_processed_at)
                    <div class="mb-2">
                        <label class="text-muted small">Thời gian xử lý</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($booking->refund_processed_at)->format('d/m/Y H:i:s') }}</p>
                    </div>
                    @endif
                    @endif

                    @if($booking->status === 'refunded')
                    <hr>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        Đơn hàng đã được hoàn tiền thành công. Vui lòng kiểm tra tài khoản của bạn.
                    </div>
                    @elseif($booking->status === 'cancelled' && $booking->refund_status === 'failed')
                    <hr>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Hoàn tiền tự động thất bại. Vui lòng liên hệ bộ phận hỗ trợ để được xử lý.
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Tour Info -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Thông tin tour</h6>
                </div>
                <div class="card-body">
                    @if($booking->schedule->tour->image)
                        <img src="{{ asset($booking->schedule->tour->image) }}" class="img-fluid rounded mb-3" alt="">
                    @endif

                    <p class="mb-2">
                        <i class="bi bi-speedometer2 text-muted me-2"></i>
                        Độ khó: {{ $booking->schedule->tour->difficulty_text }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
@if($hasPaid && $tourEnded && !$existingReview)
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="reviewModalLabel">
                    <i class="bi bi-star-fill text-warning me-2"></i>Đánh giá tour
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Tour Info -->
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4">
                    @if($booking->schedule->tour->image)
                    <img src="{{ asset($booking->schedule->tour->image) }}" 
                         alt="{{ $booking->schedule->tour->name }}" 
                         class="rounded"
                         style="width: 80px; height: 60px; object-fit: cover;">
                    @endif
                    <div>
                        <h6 class="fw-bold mb-1">{{ $booking->schedule->tour->name }}</h6>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('d/m/Y') }}
                            @if($booking->schedule->end_date)
                            - {{ \Carbon\Carbon::parse($booking->schedule->end_date)->format('d/m/Y') }}
                            @endif
                        </small>
                    </div>
                </div>

                <form method="POST" action="{{ route('reviews.store') }}" id="reviewForm">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $booking->schedule->tour->id }}">
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                    <!-- Rating Stars -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-3">
                            Đánh giá của bạn <span class="text-danger">*</span>
                        </label>
                        <div class="star-rating-input d-flex gap-1 justify-content-center" id="starRating">
                            @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="d-none" required>
                            <label for="star{{ $i }}" class="star-label" data-value="{{ $i }}">
                                <i class="bi bi-star-fill"></i>
                            </label>
                            @endfor
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted" id="ratingText">Chọn số sao để đánh giá</small>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="reviewTitle" class="form-label fw-semibold">
                            Tiêu đề đánh giá
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="reviewTitle" 
                               name="title"
                               placeholder="Ví dụ: Trải nghiệm tuyệt vời!"
                               maxlength="255"
                               style="border-radius: 8px; padding: 12px 16px;">
                    </div>

                    <!-- Comment -->
                    <div class="mb-4">
                        <label for="reviewComment" class="form-label fw-semibold">
                            Nhận xét chi tiết
                        </label>
                        <textarea class="form-control" 
                                  id="reviewComment" 
                                  name="comment" 
                                  rows="4"
                                  placeholder="Chia sẻ chi tiết về trải nghiệm của bạn với tour này..."
                                  maxlength="1000"
                                  style="border-radius: 8px; padding: 12px 16px;"></textarea>
                        <div class="form-text">Tối đa 1000 ký tự</div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-2"></i>Gửi đánh giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Star Rating Styles */
.star-rating-input {
    font-size: 40px;
}

.star-label {
    cursor: pointer;
    color: #e2e8f0;
    transition: all 0.2s ease;
    padding: 0 4px;
}

.star-label:hover,
.star-label.active {
    color: #fbbf24;
    transform: scale(1.1);
}

.star-label.hovered {
    color: #fbbf24;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starLabels = document.querySelectorAll('.star-label');
    const ratingText = document.getElementById('ratingText');
    const ratingTexts = {
        1: '⭐ Rất tệ',
        2: '⭐⭐ Tệ', 
        3: '⭐⭐⭐ Bình thường',
        4: '⭐⭐⭐⭐ Tốt',
        5: '⭐⭐⭐⭐⭐ Tuyệt vời'
    };

    starLabels.forEach((label, index) => {
        // Hover effect
        label.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            starLabels.forEach((l, i) => {
                if (i < value) {
                    l.classList.add('hovered');
                } else {
                    l.classList.remove('hovered');
                }
            });
        });

        // Click to select
        label.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            starLabels.forEach((l, i) => {
                if (i < value) {
                    l.classList.add('active');
                } else {
                    l.classList.remove('active');
                }
            });
            ratingText.textContent = ratingTexts[value];
            ratingText.style.color = '#fbbf24';
            ratingText.style.fontWeight = '600';
        });
    });

    // Reset hover on mouse leave
    document.getElementById('starRating').addEventListener('mouseleave', function() {
        starLabels.forEach(l => l.classList.remove('hovered'));
    });
});
</script>
@endif

<!-- Modal Hủy đơn (cho confirmed booking) -->
@if($booking->status === 'confirmed')
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 bg-danger text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="cancelBookingModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Yêu cầu hủy đơn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Đơn đã được xác nhận và đã thanh toán. Việc hủy đơn sẽ cần xử lý hoàn tiền.
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label fw-semibold">
                            Lý do hủy đơn <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="cancellation_reason" 
                                  name="cancellation_reason" 
                                  rows="4"
                                  required
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="Vui lòng cho chúng tôi biết lý do bạn muốn hủy đơn đặt vé này (ít nhất 10 ký tự)..."
                                  style="border-radius: 8px;"></textarea>
                        <div class="form-text">Lý do hủy phải có từ 10 đến 500 ký tự.</div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <h6 class="fw-bold mb-2">Thông tin đơn hàng:</h6>
                        <p class="mb-1"><strong>Mã đơn:</strong> #{{ $booking->id }}</p>
                        <p class="mb-1"><strong>Tour:</strong> {{ $booking->schedule->tour->name }}</p>
                        <p class="mb-1"><strong>Đã thanh toán:</strong> {{ number_format($booking->paid_amount, 0, ',', '.') }}đ</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Xác nhận hủy đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.getElementById('btnCancelBooking')?.addEventListener('click', function() {
    const form = document.getElementById('cancelForm');
    confirmCancel('Bạn có chắc muốn hủy đơn đặt vé này?<br><small class="text-muted">Nếu đã thanh toán, bạn sẽ được hoàn tiền theo chính sách.</small>', function() {
        form.submit();
    });
});
</script>
@endpush
@endsection
