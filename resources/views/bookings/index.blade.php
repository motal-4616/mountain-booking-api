@extends('layouts.app')

@section('title', 'Vé đã đặt')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-ticket-perforated text-success me-2"></i>Vé của tôi
        </h2>
        <a href="{{ route('tours.index') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Đặt vé mới
        </a>
    </div>

    @if($bookings->count() > 0)
        <div class="row g-4">
            @foreach($bookings as $booking)
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if($booking->schedule->tour->image)
                                        <img src="{{ asset($booking->schedule->tour->image) }}" class="img-fluid rounded" alt="">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=200" class="img-fluid rounded" alt="">
                                    @endif
                                </div>

                                <div class="col-md-5">
                                    <h5 class="mb-2">{{ $booking->schedule->tour->name }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-calendar3 me-2"></i>{{ $booking->schedule->formatted_date }}
                                    </p>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-people me-2"></i>{{ $booking->quantity }} người
                                    </p>
                                    @if(!in_array($booking->status, ['cancelled', 'refund_processing', 'refunded']) && $booking->schedule->days_until_departure > 0)
                                        <p class="mb-1">
                                            <span class="badge bg-{{ $booking->schedule->days_until_departure <= 7 ? 'warning' : 'info' }} px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i>{{ $booking->schedule->time_until_departure }}
                                            </span>
                                        </p>
                                    @endif
                                    
                                    @if(in_array($booking->status, ['cancelled', 'refund_processing', 'refunded']))
                                        @if($booking->status === 'refund_processing')
                                            <p class="text-warning mb-1 small">
                                                <i class="bi bi-hourglass-split me-1"></i>Đang xử lý hoàn tiền...
                                            </p>
                                        @endif
                                        @if($booking->cancelled_at)
                                            <p class="text-danger mb-1 small">
                                                <i class="bi bi-x-circle me-1"></i>Đã hủy: {{ $booking->cancelled_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                        @if($booking->cancellation_reason)
                                            <p class="text-muted mb-1 small fst-italic">
                                                <i class="bi bi-info-circle me-1"></i>{{ Str::limit($booking->cancellation_reason, 60) }}
                                            </p>
                                        @endif
                                    @endif
                                    
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-clock me-2"></i>Đặt lúc: {{ $booking->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <div class="col-md-2 text-center">
                                    <!-- Trạng thái đơn -->
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $booking->status_badge }} px-3 py-2">
                                            {{ $booking->status_text }}
                                        </span>
                                    </div>
                                    
                                    <!-- Thông tin thanh toán -->
                                    @if($booking->status === 'refund_processing')
                                        <small class="text-warning d-block">
                                            <i class="bi bi-hourglass-split me-1"></i>Đang hoàn tiền
                                        </small>
                                    @elseif(($booking->status === 'cancelled' && $booking->payment_status === 'refunded') || $booking->status === 'refunded')
                                        <small class="text-dark d-block">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Đã hoàn {{ number_format($booking->paid_amount, 0, ',', '.') }}đ
                                        </small>
                                    @elseif($booking->status === 'refunded')
                                        <small class="text-dark d-block">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            @if($booking->payment_status === 'partial')
                                                Hoàn cọc {{ number_format($booking->paid_amount, 0, ',', '.') }}đ
                                            @else
                                                Đã hoàn {{ number_format($booking->paid_amount, 0, ',', '.') }}đ
                                            @endif
                                        </small>
                                    @elseif($booking->payment_status === 'paid')
                                        <small class="text-success d-block">
                                            <i class="bi bi-check-circle me-1"></i>Đã thanh toán đủ
                                        </small>
                                    @elseif($booking->payment_status === 'partial')
                                        <small class="text-warning d-block">
                                            <i class="bi bi-hourglass me-1"></i>Đã cọc {{ number_format($booking->paid_amount, 0, ',', '.') }}đ
                                        </small>
                                    @elseif($booking->payment_status === 'unpaid')
                                        <small class="text-danger d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i>Chưa thanh toán
                                        </small>
                                    @endif
                                </div>

                                <div class="col-md-2 text-center">
                                    @if($booking->discount_amount > 0)
                                        <div class="mb-1">
                                            <small class="text-muted text-decoration-line-through">
                                                {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                                            </small>
                                        </div>
                                        <span class="h5 text-danger">{{ $booking->formatted_price }}</span>
                                        <div class="mt-1">
                                            <small class="badge bg-danger">
                                                -{{ number_format($booking->discount_amount, 0, ',', '.') }}đ
                                            </small>
                                        </div>
                                    @else
                                        <span class="h5 text-danger">{{ $booking->formatted_price }}</span>
                                    @endif
                                </div>

                                <div class="col-md-1 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('bookings.show', $booking) }}">
                                                    <i class="bi bi-eye me-2"></i>Xem chi tiết
                                                </a>
                                            </li>
                                            @if($booking->status === 'pending' && $booking->schedule->days_until_departure > 0)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="cancel-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="dropdown-item text-danger btn-cancel-booking" data-id="{{ $booking->id }}">
                                                            <i class="bi bi-x-circle me-2"></i>Hủy đơn
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $bookings->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-ticket fs-1 text-muted"></i>
            <h4 class="mt-3 text-muted">Bạn chưa đặt vé nào</h4>
            <p class="text-muted">Hãy khám phá các tour leo núi và đặt vé ngay!</p>
            <a href="{{ route('tours.index') }}" class="btn btn-success mt-2">
                <i class="bi bi-compass me-2"></i>Xem Tour
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-cancel-booking').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const id = this.dataset.id;
        
        confirmCancel(`Bạn có chắc muốn hủy đơn <strong>#${id}</strong>?<br><small class="text-muted">Nếu đã thanh toán, bạn sẽ được hoàn tiền theo chính sách.</small>`, function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
