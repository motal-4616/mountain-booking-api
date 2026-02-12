@extends('admin.layouts.app')

@section('title', 'Chi tiết Đơn #' . $booking->id)
@section('page-title', 'Chi tiết Đơn Đặt Vé #' . $booking->id)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Thông tin đơn -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
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
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Số lượng người</label>
                        <p class="mb-0">{{ $booking->quantity }} người</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Giá tour/người</label>
                        <p class="mb-0">{{ number_format($booking->schedule->price, 0, ',', '.') }}đ</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tổng giá gốc</label>
                        <p class="mb-0 fw-bold fs-5">{{ $booking->formatted_total_amount }}</p>
                    </div>
                    @if($booking->discount_amount > 0)
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Giảm giá</label>
                        <p class="mb-0 text-danger fw-bold fs-5">-{{ number_format($booking->discount_amount, 0, ',', '.') }}đ</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Thành tiền</label>
                        <p class="mb-0 text-primary fw-bold fs-5">{{ $booking->formatted_price }}</p>
                    </div>
                    @endif
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Đã thanh toán</label>
                        <p class="mb-0 text-success fw-bold fs-5">{{ $booking->formatted_paid_amount }}</p>
                    </div>
                    @if($booking->remaining_amount > 0)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Còn thiếu</label>
                            <p class="mb-0 text-danger fw-bold">{{ $booking->formatted_remaining_amount }}</p>
                        </div>
                    @endif
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Trạng thái thanh toán</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $booking->payment_status_badge }}">
                                {{ $booking->payment_status_text }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Trạng thái đơn</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $booking->status_badge }}">
                                {{ $booking->status_text }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Ngày đặt</label>
                        <p class="mb-0">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tài khoản đặt</label>
                        <p class="mb-0">{{ $booking->user->name }} ({{ $booking->user->email }})</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <div class="card border-0 shadow-sm">
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
                        <p class="mb-0">
                            <a href="tel:{{ $booking->contact_phone }}">{{ $booking->contact_phone }}</a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $booking->contact_email }}">{{ $booking->contact_email }}</a>
                        </p>
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

        <!-- Lịch sử thanh toán -->
        @if($booking->payments->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử thanh toán</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                        <thead style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <tr>
                                <th class="text-muted fw-semibold py-3 px-3">
                                    <i class="bi bi-calendar3 me-1"></i>Thời gian
                                </th>
                                <th class="text-muted fw-semibold py-3">
                                    <i class="bi bi-tag me-1"></i>Loại
                                </th>
                                <th class="text-muted fw-semibold py-3">
                                    <i class="bi bi-wallet2 me-1"></i>Phương thức
                                </th>
                                <th class="text-muted fw-semibold py-3 text-end">
                                    <i class="bi bi-cash me-1"></i>Số tiền
                                </th>
                                <th class="text-muted fw-semibold py-3 text-center">
                                    <i class="bi bi-info-circle me-1"></i>Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->payments as $payment)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td class="py-3 px-3">
                                    <div class="text-dark">{{ $payment->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                        {{ $payment->payment_type_text }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    @if($payment->payment_method === 'vnpay')
                                        <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <i class="bi bi-credit-card me-1"></i>VNPay
                                        </span>
                                    @elseif($payment->payment_method === 'cash')
                                        <span class="badge bg-success">
                                            <i class="bi bi-cash-stack me-1"></i>Tiền mặt
                                        </span>
                                    @else
                                        <span class="badge bg-primary">
                                            <i class="bi bi-bank me-1"></i>Chuyển khoản
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-end">
                                    <span class="fw-bold text-primary" style="font-size: 15px;">
                                        {{ $payment->formatted_amount }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-{{ $payment->status_badge }} px-3 py-2">
                                        @if($payment->status === 'success')
                                            <i class="bi bi-check-circle me-1"></i>
                                        @elseif($payment->status === 'pending')
                                            <i class="bi bi-clock me-1"></i>
                                        @elseif($payment->status === 'failed')
                                            <i class="bi bi-x-circle me-1"></i>
                                        @else
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        @endif
                                        {{ $payment->status_text }}
                                    </span>
                                </td>
                            </tr>
                            @if($payment->note || $payment->transaction_ref || $payment->confirmedByUser)
                            <tr style="background: #f8f9fa;">
                                <td colspan="5" class="py-2 px-3">
                                    <div class="d-flex flex-wrap gap-3 small">
                                        @if($payment->transaction_ref)
                                            <span class="text-muted">
                                                <i class="bi bi-hash text-primary"></i>
                                                <strong>Mã GD:</strong> <code class="text-dark">{{ $payment->transaction_ref }}</code>
                                            </span>
                                        @endif
                                        @if($payment->note)
                                            <span class="text-muted">
                                                <i class="bi bi-chat-left-text text-info"></i>
                                                <strong>Ghi chú:</strong> {{ $payment->note }}
                                            </span>
                                        @endif
                                        @if($payment->confirmedByUser)
                                            <span class="text-muted">
                                                <i class="bi bi-person-check text-success"></i>
                                                <strong>Xác nhận:</strong> {{ $payment->confirmedByUser->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Thao tác</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
                </a>

                <!-- Xác nhận thanh toán tại điểm tập kết -->
                @if($booking->payment_status === 'partial' && $booking->status !== 'cancelled')
                    <form action="{{ route('admin.bookings.confirmPayment', $booking) }}" method="POST" class="mb-2" id="confirmPaymentForm">
                        @csrf
                        @method('PATCH')
                        <button type="button" class="btn btn-success w-100" id="btnConfirmPayment" data-amount="{{ $booking->formatted_remaining_amount }}">
                            <i class="bi bi-cash-stack me-2"></i>Xác nhận thanh toán đủ
                        </button>
                    </form>
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Khách còn thiếu <strong>{{ $booking->formatted_remaining_amount }}</strong>
                    </div>
                @endif

                @if($booking->status === 'pending')
                    <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận đơn
                        </button>
                    </form>
                @endif

                @if(in_array($booking->status, ['pending', 'confirmed']))
                    @if($booking->status === 'confirmed')
                        <!-- Đơn confirmed: cần nhập lý do hủy -->
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                            <i class="bi bi-x-circle me-2"></i>Hủy đơn
                        </button>
                    @else
                        <!-- Đơn pending: hủy không cần lý do -->
                        <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" id="cancelPendingForm">
                            @csrf
                            @method('PATCH')
                            <button type="button" class="btn btn-danger w-100" id="btnCancelPending">
                                <i class="bi bi-x-circle me-2"></i>Hủy đơn
                            </button>
                        </form>
                    @endif
                @endif

                @if(in_array($booking->status, ['cancelled', 'refunded', 'refund_processing']))
                    <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" id="deleteBookingForm">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger w-100" id="btnDeleteBooking">
                            <i class="bi bi-trash me-2"></i>Xóa đơn
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Tour Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Thông tin Tour</h6>
            </div>
            <div class="card-body">
                @if($booking->schedule->tour->image)
                    <img src="{{ asset($booking->schedule->tour->image) }}" class="img-fluid rounded mb-3" alt="">
                @endif

                <p class="mb-2"><i class="bi bi-geo-alt text-muted me-2"></i>{{ $booking->schedule->tour->location }}</p>
                <p class="mb-2">
                    <i class="bi bi-speedometer2 text-muted me-2"></i>
                    Độ khó: {{ $booking->schedule->tour->difficulty_text }}
                </p>
                <p class="mb-0">
                    <i class="bi bi-cash text-muted me-2"></i>
                    {{ number_format($booking->schedule->price, 0, ',', '.') }}đ / người
                </p>
            </div>
        </div>

        <!-- Thông tin hủy đơn (nếu đã bị hủy) -->
        @if(in_array($booking->status, ['cancelled', 'refunded', 'refund_processing']))
        <div class="card border-0 shadow-sm border-danger mt-4">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-x-circle me-2"></i>Thông tin hủy đơn</h6>
            </div>
            <div class="card-body">
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
                <div class="mb-0">
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
                
                {{-- Thông tin hoàn tiền --}}
                @if($booking->refund_status)
                <hr>
                <h6 class="fw-bold mb-3"><i class="bi bi-arrow-counterclockwise me-2"></i>Thông tin hoàn tiền VNPay</h6>
                <div class="mb-2">
                    <label class="text-muted small">Trạng thái hoàn tiền</label>
                    <p class="mb-0">
                        @if($booking->refund_status === 'processing')
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Đang xử lý</span>
                        @elseif($booking->refund_status === 'success')
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Thành công</span>
                        @elseif($booking->refund_status === 'failed')
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Thất bại</span>
                        @endif
                    </p>
                </div>
                @if($booking->refund_message)
                <div class="mb-2">
                    <label class="text-muted small">Chi tiết</label>
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
                    <p class="mb-0">{{ \Carbon\Carbon::parse($booking->refund_processed_at)->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @endif

                @if($booking->status === 'refunded')
                <hr>
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    Đơn hàng đã được xử lý hoàn tiền thành công.
                </div>
                @elseif($booking->status === 'refund_processing')
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-hourglass-split me-2"></i>
                    Đang xử lý hoàn tiền <strong>{{ number_format($booking->paid_amount, 0, ',', '.') }}đ</strong> qua VNPay.
                </div>
                @elseif($booking->refund_status === 'failed' && $booking->paid_amount > 0)
                <hr>
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Hoàn tiền VNPay thất bại. Cần xử lý hoàn tiền <strong>{{ number_format($booking->paid_amount, 0, ',', '.') }}đ</strong> thủ công cho khách hàng.
                </div>
                {{-- Nút thử lại hoàn tiền --}}
                <form action="{{ route('admin.bookings.retryRefund', $booking) }}" method="POST" class="mt-2" id="retryRefundForm">
                    @csrf
                    <button type="button" class="btn btn-warning btn-sm" id="btnRetryRefund">
                        <i class="bi bi-arrow-clockwise me-1"></i>Thử lại hoàn tiền VNPay
                    </button>
                </form>

                {{-- Nút xác nhận hoàn tiền thủ công --}}
                <div class="mt-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#manualRefundCollapse">
                        <i class="bi bi-check2-circle me-1"></i>Xác nhận đã hoàn tiền thủ công
                    </button>
                    <div class="collapse mt-2" id="manualRefundCollapse">
                        <div class="card card-body" style="border-radius: 12px;">
                            <form action="{{ route('admin.bookings.manualRefund', $booking) }}" method="POST" id="manualRefundForm">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Ghi chú hoàn tiền <span class="text-danger">*</span></label>
                                    <textarea name="refund_note" class="form-control form-control-sm" rows="2" 
                                        placeholder="VD: Đã chuyển khoản hoàn tiền cho khách qua ngân hàng Vietcombank" required minlength="5"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Xác nhận đã hoàn tiền {{ number_format($booking->paid_amount, 0, ',', '.') }}đ cho khách hàng?')">
                                    <i class="bi bi-check-lg me-1"></i>Xác nhận hoàn tiền {{ number_format($booking->paid_amount, 0, ',', '.') }}đ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- Debug: Hiển thị chi tiết payment VNPay để debug --}}
                <div class="alert alert-secondary mt-2 mb-0" style="font-size: 12px;">
                    <strong><i class="bi bi-bug me-1"></i>Debug Refund Info:</strong>
                    <ul class="mb-0 mt-1">
                        <li>Refund Message: <code>{{ $booking->refund_message }}</code></li>
                        @php
                            $vnpPayments = $booking->payments()->where('payment_method', 'vnpay')->get();
                        @endphp
                        @foreach($vnpPayments as $vp)
                        <li>
                            Payment #{{ $vp->id }} [{{ $vp->status }}]: 
                            amount=<code>{{ $vp->amount }}</code>, 
                            transaction_ref(vnp_TxnRef)=<code>{{ $vp->transaction_ref ?? 'NULL' }}</code>, 
                            vnp_transaction_no=<code>{{ $vp->vnp_transaction_no ?? 'NULL' }}</code>,
                            vnp_pay_date=<code>{{ $vp->vnp_pay_date ?? 'NULL' }}</code>,
                            created_at=<code>{{ $vp->created_at?->format('YmdHis') }}</code>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @elseif($booking->paid_amount > 0 && !$booking->refund_status)
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Cần xử lý hoàn tiền <strong>{{ number_format($booking->paid_amount, 0, ',', '.') }}đ</strong> cho khách hàng.
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Hủy đơn (cho confirmed booking) -->
@if($booking->status === 'confirmed')
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 bg-danger text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="cancelBookingModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Hủy đơn đặt vé
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Đơn đã được xác nhận. Nếu khách đã thanh toán, cần xử lý hoàn tiền.
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
                                  placeholder="Vui lòng nhập lý do hủy đơn (ít nhất 10 ký tự)..."
                                  style="border-radius: 8px;"></textarea>
                        <div class="form-text">Lý do hủy sẽ được gửi cho khách hàng.</div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <h6 class="fw-bold mb-2">Thông tin đơn hàng:</h6>
                        <p class="mb-1"><strong>Mã đơn:</strong> #{{ $booking->id }}</p>
                        <p class="mb-1"><strong>Khách hàng:</strong> {{ $booking->contact_name }}</p>
                        <p class="mb-1"><strong>Tour:</strong> {{ $booking->schedule->tour->name }}</p>
                        <p class="mb-1"><strong>Đã thanh toán:</strong> {{ number_format($booking->paid_amount, 0, ',', '.') }}đ</p>
                        @if($booking->paid_amount > 0)
                        <p class="mb-0 text-danger"><strong>Cần hoàn tiền:</strong> {{ number_format($booking->paid_amount, 0, ',', '.') }}đ</p>
                        @endif
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
// Xác nhận thanh toán
document.getElementById('btnConfirmPayment')?.addEventListener('click', function() {
    const form = document.getElementById('confirmPaymentForm');
    const amount = this.dataset.amount;
    
    confirmAction(`Xác nhận khách đã thanh toán đủ <strong>${amount}</strong> tại điểm tập kết?`, function() {
        form.submit();
    }, {
        title: 'Xác nhận thanh toán',
        confirmText: 'Xác nhận'
    });
});

// Hủy đơn pending
document.getElementById('btnCancelPending')?.addEventListener('click', function() {
    const form = document.getElementById('cancelPendingForm');
    confirmCancel('Bạn có chắc muốn hủy đơn này?', function() {
        form.submit();
    });
});

// Xóa đơn
document.getElementById('btnDeleteBooking')?.addEventListener('click', function() {
    const form = document.getElementById('deleteBookingForm');
    confirmDelete('Xóa vĩnh viễn đơn này?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>', function() {
        form.submit();
    });
});

// Thử lại hoàn tiền
document.getElementById('btnRetryRefund')?.addEventListener('click', function() {
    const form = document.getElementById('retryRefundForm');
    confirmAction('Bạn có chắc muốn thử lại hoàn tiền VNPay cho đơn này?', function() {
        form.submit();
    }, {
        title: 'Thử lại hoàn tiền',
        confirmText: 'Thử lại'
    });
});
</script>
@endpush
@endsection
