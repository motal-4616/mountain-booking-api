@extends('admin.layouts.app')

@section('title', 'Chi tiết Đánh giá #' . $review->id)
@section('page-title', 'Chi tiết Đánh giá')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}" class="text-decoration-none">Đánh giá</a></li>
            <li class="breadcrumb-item active">#{{ $review->id }}</li>
        </ol>
    </nav>
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-modern btn-outline-modern">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row g-4">
    <!-- Review Content -->
    <div class="col-lg-8">
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i>Nội dung đánh giá</h5>
            </div>
            <div class="card-body-modern">
                <!-- User Info -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                    <img src="{{ $review->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                         alt="{{ $review->user->name }}" 
                         class="rounded-circle"
                         width="64" height="64"
                         style="object-fit: cover;">
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $review->user->name }}</h5>
                        <div class="text-muted">{{ $review->user->email }}</div>
                        <div class="text-muted small">{{ $review->user->phone ?? 'Chưa cập nhật SĐT' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1">{!! $review->status_badge !!}</div>
                        <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>

                <!-- Rating -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted mb-2">Đánh giá</label>
                    <div class="d-flex align-items-center gap-2">
                        <div style="font-size: 32px;">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="bi bi-star-fill text-warning"></i>
                                @else
                                    <i class="bi bi-star text-muted"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="fs-4 fw-bold">{{ $review->rating }}/5</span>
                    </div>
                </div>

                <!-- Title -->
                @if($review->title)
                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted mb-2">Tiêu đề</label>
                    <h4 class="fw-bold">{{ $review->title }}</h4>
                </div>
                @endif

                <!-- Comment -->
                @if($review->comment)
                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted mb-2">Nhận xét</label>
                    <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">{{ $review->comment }}</p>
                </div>
                @endif

                <!-- Admin Note -->
                @if($review->admin_note)
                <div class="alert alert-info">
                    <strong><i class="bi bi-info-circle me-2"></i>Ghi chú của Admin:</strong>
                    <p class="mb-0 mt-2">{{ $review->admin_note }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Update Status Form -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Cập nhật trạng thái</h5>
            </div>
            <div class="card-body-modern">
                <form method="POST" action="{{ route('admin.reviews.updateStatus', $review->id) }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="pending" id="statusPending"
                                       {{ $review->status === 'pending' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusPending">
                                    <i class="bi bi-clock text-warning me-1"></i>Chờ duyệt
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="approved" id="statusApproved"
                                       {{ $review->status === 'approved' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusApproved">
                                    <i class="bi bi-check-circle text-success me-1"></i>Duyệt
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="rejected" id="statusRejected"
                                       {{ $review->status === 'rejected' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusRejected">
                                    <i class="bi bi-x-circle text-danger me-1"></i>Từ chối
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="admin_note" class="form-label fw-semibold">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control form-control-modern" 
                                  id="admin_note" 
                                  name="admin_note" 
                                  rows="3"
                                  placeholder="Thêm ghi chú về quyết định của bạn..."
                                  maxlength="500">{{ $review->admin_note }}</textarea>
                        <div class="form-text">Tối đa 500 ký tự</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-modern btn-primary-modern">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" 
                      method="POST" id="deleteReviewForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-modern btn-danger-modern" id="btnDeleteReview">
                        <i class="bi bi-trash me-2"></i>Xóa đánh giá
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Tour Info -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5 class="mb-0"><i class="bi bi-compass me-2"></i>Thông tin Tour</h5>
            </div>
            <div class="card-body-modern">
                <img src="{{ asset($review->tour->image) }}" 
                     alt="{{ $review->tour->name }}" 
                     class="img-fluid rounded mb-3">
                <h6 class="fw-bold mb-2">{{ $review->tour->name }}</h6>
                <div class="mb-2">
                    <i class="bi bi-geo-alt text-muted me-2"></i>{{ $review->tour->location }}
                </div>
                <div class="mb-3">
                    <i class="bi bi-tag text-muted me-2"></i>{{ $review->tour->formatted_price }}
                </div>
                <a href="{{ route('tours.show', $review->tour_id) }}" 
                   class="btn btn-modern btn-outline-modern btn-sm w-100" 
                   target="_blank">
                    <i class="bi bi-eye me-2"></i>Xem tour
                </a>
            </div>
        </div>

        <!-- Booking Info -->
        @if($review->booking)
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Thông tin Booking</h5>
            </div>
            <div class="card-body-modern">
                <div class="mb-2">
                    <strong>Mã booking:</strong> 
                    <span class="badge badge-modern badge-secondary">#{{ $review->booking->id }}</span>
                </div>
                <div class="mb-2">
                    <strong>Lịch khởi hành:</strong><br>
                    <small class="text-muted">{{ optional($review->booking->schedule)->departure_date?->format('d/m/Y') ?? 'N/A' }}</small>
                </div>
                <div class="mb-2">
                    <strong>Số lượng:</strong> {{ $review->booking->quantity }} người
                </div>
                <div class="mb-2">
                    <strong>Tổng tiền:</strong> {{ number_format($review->booking->total_amount, 0, ',', '.') }} VNĐ
                </div>
                <div class="mb-3">
                    <strong>Trạng thái:</strong>
                    @if($review->booking->status === 'completed')
                        <span class="badge bg-success">Hoàn thành</span>
                    @else
                        <span class="badge bg-secondary">{{ $review->booking->status_text }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.bookings.show', $review->booking->id) }}" 
                   class="btn btn-modern btn-outline-modern btn-sm w-100">
                    <i class="bi bi-eye me-2"></i>Xem booking
                </a>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Thống kê User</h5>
            </div>
            <div class="card-body-modern">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tổng booking:</span>
                    <strong>{{ $review->user->bookings()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tổng đánh giá:</span>
                    <strong>{{ $review->user->reviews()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Đánh giá TB:</span>
                    <strong>
                        {{ number_format($review->user->reviews()->avg('rating') ?? 0, 1) }} 
                        <i class="bi bi-star-fill text-warning"></i>
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnDeleteReview')?.addEventListener('click', function() {
    const form = document.getElementById('deleteReviewForm');
    confirmDelete('Bạn có chắc muốn xóa đánh giá này?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>', function() {
        form.submit();
    });
});
</script>
@endpush
@endsection
