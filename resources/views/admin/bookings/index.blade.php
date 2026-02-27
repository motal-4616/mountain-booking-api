@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn Đặt Vé')
@section('page-title', 'Quản lý Đơn Đặt Vé')

@section('content')
<!-- Header với filter -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" class="form-control form-control-modern" name="search"
                       value="{{ request('search') }}" placeholder="Tìm theo tên, email, SĐT, mã đơn...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-bag-check me-1"></i>Trạng thái đơn
                </label>
                <select class="form-select form-control-modern" name="status">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="refund_processing" {{ request('status') == 'refund_processing' ? 'selected' : '' }}>Đang hoàn tiền</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-credit-card me-1"></i>Thanh toán
                </label>
                <select class="form-select form-control-modern" name="payment">
                    <option value="">Tất cả</option>
                    <option value="paid" {{ request('payment') == 'paid' ? 'selected' : '' }}>Đã TT đủ</option>
                    <option value="partial" {{ request('payment') == 'partial' ? 'selected' : '' }}>TT một phần</option>
                    <option value="unpaid" {{ request('payment') == 'unpaid' ? 'selected' : '' }}>Chưa TT</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                @if(request()->hasAny(['search', 'status', 'payment']))
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-modern btn-secondary-modern w-100">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Danh sách đơn đặt -->
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-receipt"></i> Danh sách Đơn Đặt Vé</h5>
        <span class="badge-modern badge-primary">{{ $bookings->total() }} đơn</span>
    </div>
    <div class="card-body-modern p-0">
        @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th>Ngày đi</th>
                            <th>Số người</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th width="140">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary">#{{ $booking->booking_code ?? $booking->id }}</span>
                                    <div class="text-muted small">
                                        {{ $booking->created_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle" style="width:36px;height:36px;background:linear-gradient(135deg, #10b981 0%, #059669 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:14px;">
                                            {{ strtoupper(substr($booking->contact_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $booking->contact_name }}</div>
                                            <small class="text-muted">
                                                <i class="bi bi-telephone me-1"></i>{{ $booking->contact_phone }}
                                            </small>
                                            @if($booking->user && $booking->user->current_level > 1)
                                                @php $lvl = $booking->user->level_info; @endphp
                                                @if($lvl)
                                                    <div><span class="badge" style="font-size:9px;padding:1px 6px;background:{{ $lvl->frame_color === 'gold' ? '#f59e0b' : ($lvl->frame_color === 'silver' ? '#9ca3af' : ($lvl->frame_color === 'green' ? '#10b981' : ($lvl->frame_color === 'diamond' ? '#3b82f6' : ($lvl->frame_color === 'legendary' ? '#8b5cf6' : '#6b7280')))) }};color:#fff;">{{ $lvl->icon }} Lv.{{ $lvl->level }}</span></div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($booking->schedule->tour->name, 20) }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($booking->schedule->tour->location ?? '', 15) }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success bg-opacity-10 rounded-2 px-2 py-1 text-center">
                                            <div class="fw-bold text-success small">
                                                {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('d/m') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-modern badge-info">
                                        <i class="bi bi-people me-1"></i>{{ $booking->quantity ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $booking->formatted_price }}</div>
                                    @if($booking->discount_amount > 0)
                                        <small class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                            <i class="bi bi-tag-fill"></i> -{{ number_format($booking->discount_amount, 0, ',', '.') }}đ
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->payment_status === 'paid')
                                        <span class="badge-modern badge-success">
                                            <i class="bi bi-check-circle"></i> Đã TT đủ
                                        </span>
                                    @elseif($booking->payment_status === 'partial')
                                        <span class="badge-modern badge-warning">
                                            <i class="bi bi-clock-history"></i> TT một phần
                                        </span>
                                    @elseif($booking->payment_status === 'refunded')
                                        <span class="badge-modern badge-secondary">
                                            <i class="bi bi-arrow-counterclockwise"></i> Đã hoàn tiền
                                        </span>
                                    @else
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-x-circle"></i> Chưa TT
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === 'completed')
                                        <span class="badge-modern badge-success">
                                            <i class="bi bi-check-circle-fill"></i> Hoàn thành
                                        </span>
                                    @elseif($booking->status === 'confirmed')
                                        <span class="badge-modern badge-info">
                                            <i class="bi bi-check-circle"></i> Đã XN
                                        </span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge-modern badge-warning">
                                            <i class="bi bi-clock"></i> Chờ XN
                                        </span>
                                    @elseif($booking->status === 'refund_processing')
                                        <span class="badge-modern badge-warning">
                                            <i class="bi bi-hourglass-split"></i> Đang hoàn tiền
                                        </span>
                                    @elseif($booking->status === 'refunded')
                                        <span class="badge-modern badge-info">
                                            <i class="bi bi-arrow-counterclockwise"></i> Đã hoàn tiền
                                        </span>
                                    @else
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-x-circle"></i> Đã hủy
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" 
                                           class="btn btn-sm btn-outline-info rounded-pill" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($booking->status === 'pending')
                                            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Xác nhận">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($booking->status !== 'cancelled' && $booking->status !== 'completed')
                                            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="d-inline cancel-form">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-cancel-booking" 
                                                        title="Hủy đơn" data-id="{{ $booking->id }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="card-body-modern border-top">
                    {{ $bookings->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <h3>Không tìm thấy đơn đặt vé nào</h3>
                <p>Thử thay đổi điều kiện lọc hoặc chờ khách hàng đặt tour</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Hủy booking với Popup
document.querySelectorAll('.btn-cancel-booking').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const id = this.dataset.id;
        
        confirmCancel(`Bạn có chắc muốn hủy đơn <strong>#${id}</strong>?<br><small class="text-muted">Nếu khách đã thanh toán, bạn cần xử lý hoàn tiền.</small>`, function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
