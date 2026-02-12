@extends('layouts.app')

@section('title', 'Đánh giá của tôi')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card-modern">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Tài khoản</h5>
                    <nav class="nav flex-column gap-1">
                        <a href="{{ route('user.profile') }}" class="nav-link">
                            <i class="bi bi-person me-2"></i>Hồ sơ
                        </a>
                        <a href="{{ route('bookings.index') }}" class="nav-link">
                            <i class="bi bi-ticket-perforated me-2"></i>Vé của tôi
                        </a>
                        <a href="{{ route('user.wishlist') }}" class="nav-link">
                            <i class="bi bi-heart me-2"></i>Yêu thích
                        </a>
                        <a href="{{ route('reviews.my-reviews') }}" class="nav-link active">
                            <i class="bi bi-star me-2"></i>Đánh giá
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card-modern">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-4">
                        <i class="bi bi-star-fill text-warning me-2"></i>Đánh giá của tôi
                    </h3>

                    @if($reviews->isEmpty())
                        <div class="empty-state py-5 text-center">
                            <i class="bi bi-star display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Bạn chưa có đánh giá nào</h5>
                            <p class="text-muted">Hãy hoàn thành tour và chia sẻ trải nghiệm của bạn!</p>
                            <a href="{{ route('tours.index') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-compass me-2"></i>Khám phá tour
                            </a>
                        </div>
                    @else
                        <div class="reviews-list">
                            @foreach($reviews as $review)
                                <div class="review-item border-bottom pb-4 mb-4">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <img src="{{ asset($review->tour->image) }}" 
                                                 alt="{{ $review->tour->name }}" 
                                                 class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h5 class="fw-bold mb-1">
                                                        <a href="{{ route('tours.show', $review->tour_id) }}" class="text-dark text-decoration-none">
                                                            {{ $review->tour->name }}
                                                        </a>
                                                    </h5>
                                                    <div class="mb-2">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                <i class="bi bi-star-fill text-warning"></i>
                                                            @else
                                                                <i class="bi bi-star text-muted"></i>
                                                            @endif
                                                        @endfor
                                                        <span class="text-muted ms-2">{{ $review->rating }}/5</span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    {!! $review->status_badge !!}
                                                    <div class="text-muted small mt-1">
                                                        {{ $review->created_at->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                            </div>

                                            @if($review->title)
                                                <h6 class="fw-semibold mb-2">{{ $review->title }}</h6>
                                            @endif

                                            @if($review->comment)
                                                <p class="text-muted mb-2">{{ $review->comment }}</p>
                                            @endif

                                            @if($review->status === 'rejected' && $review->admin_note)
                                                <div class="alert alert-danger alert-sm mt-2">
                                                    <i class="bi bi-exclamation-circle me-2"></i>
                                                    <strong>Lý do từ chối:</strong> {{ $review->admin_note }}
                                                </div>
                                            @endif

                                            @if($review->status === 'pending')
                                                <div class="mt-2">
                                                    <form action="{{ route('reviews.destroy', $review->id) }}" 
                                                          method="POST" 
                                                          class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                                            <i class="bi bi-trash me-1"></i>Xóa
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($reviews->hasPages())
                            <div class="mt-4">
                                {{ $reviews->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        confirmDelete('Bạn có chắc muốn xóa đánh giá này?', function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
