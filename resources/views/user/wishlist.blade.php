@extends('layouts.app')

@section('title', 'Tour yêu thích')

@section('content')
<div class="page-header-modern">
    <div class="container">
        <h1><i class="bi bi-heart-fill me-3"></i>Tour yêu thích của bạn</h1>
    </div>
</div>

<div class="wishlist-content">
    <div class="container">
        @if($wishlists->count() > 0)
            <div class="wishlist-grid">
                @foreach($wishlists as $wishlist)
                @if($wishlist->tour)
                <div class="tour-card">
                    <div class="tour-card-image">
                        <img src="{{ $wishlist->tour->image ? asset($wishlist->tour->image) : 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800' }}" alt="{{ $wishlist->tour->name }}">
                        <div class="tour-card-overlay">
                            <span class="tour-location">
                                <i class="bi bi-geo-alt-fill"></i> {{ $wishlist->tour->location }}
                            </span>
                        </div>
                        <button class="wishlist-btn active" onclick="removeFromWishlist({{ $wishlist->tour->id }}, this)" title="Xóa khỏi danh sách">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                    
                    <div class="tour-card-content">
                        <h3>{{ $wishlist->tour->name }}</h3>
                        <div class="tour-meta">
                            <span><i class="bi bi-geo-alt"></i> {{ $wishlist->tour->location }}</span>
                            <span><i class="bi bi-bar-chart"></i> {{ $wishlist->tour->difficulty_text }}</span>
                        </div>
                        <div class="tour-card-footer">
                            <div class="tour-price">
                                <span class="price-value">
                                    @php
                                        $activeSchedulesPrice = $wishlist->tour->schedules()
                                            ->where('is_active', true)
                                            ->where('departure_date', '>=', now())
                                            ->where('price', '>', 0)
                                            ->get();
                                        $minPrice = $activeSchedulesPrice->min('price');
                                        $maxPrice = $activeSchedulesPrice->max('price');
                                    @endphp
                                    @if($minPrice && $maxPrice && $minPrice != $maxPrice)
                                        {{ number_format($minPrice) }}đ - {{ number_format($maxPrice) }}đ
                                    @elseif($minPrice)
                                        {{ number_format($minPrice) }}đ
                                    @else
                                        <span class="text-muted">Liên hệ</span>
                                    @endif
                                </span>
                                <span class="price-unit">/người</span>
                            </div>
                            <a href="{{ route('tours.show', $wishlist->tour) }}" class="btn-view-tour">
                                Chi tiết <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            
            <div class="pagination-modern">
                {{ $wishlists->links() }}
            </div>
        @else
            <div class="empty-wishlist">
                <div class="empty-icon">
                    <i class="bi bi-heart"></i>
                </div>
                <h2>Danh sách trống</h2>
                <p>Bạn chưa lưu tour nào vào danh sách yêu thích</p>
                <a href="{{ route('tours.index') }}" class="btn-explore">
                    <i class="bi bi-compass me-2"></i> Khám phá các tour
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function removeFromWishlist(tourId, btn) {
    fetch('{{ route("user.wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ tour_id: tourId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.action === 'removed') {
            const card = btn.closest('.tour-card');
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                card.remove();
                const grid = document.querySelector('.wishlist-grid');
                if (grid && grid.children.length === 0) {
                    location.reload();
                }
                updateWishlistCount();
            }, 300);
        }
    })
    .catch(err => console.error('Error:', err));
}
</script>
@endpush
@endsection
