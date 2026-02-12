@extends('layouts.app')

@section('title', 'Danh sách Tour leo núi')

@section('content')
<div class="tours-page-modern">
    <div class="tours-page-container">
        <!-- Sidebar Filter -->
        <aside class="tours-sidebar">
            <div class="sidebar-header">
                <h3>Bộ lọc</h3>
            </div>
            
            <form method="GET" action="{{ route('tours.index') }}" class="sidebar-filter-form">
                <!-- Địa điểm -->
                <div class="filter-section">
                    <label class="filter-label">Địa điểm</label>
                    <input type="text" 
                           name="search" 
                           class="filter-input" 
                           placeholder="Tìm cả địa điểm"
                           value="{{ request('search') }}">
                </div>

                <!-- Khoảng giá -->
                <div class="filter-section">
                    <label class="filter-label">Khoảng giá (VNĐ)</label>
                    <div class="filter-price-range">
                        <input type="number" 
                               name="min_price" 
                               class="filter-input" 
                               placeholder="Từ"
                               value="{{ request('min_price') }}"
                               min="0"
                               step="100000">
                        <span class="price-separator">-</span>
                        <input type="number" 
                               name="max_price" 
                               class="filter-input" 
                               placeholder="Đến"
                               value="{{ request('max_price') }}"
                               min="0"
                               step="100000">
                    </div>
                </div>

                <!-- Độ khó -->
                <div class="filter-section">
                    <label class="filter-label">Độ khó</label>
                    <div class="filter-checkbox-group">
                        <label class="filter-checkbox">
                            <input type="checkbox" name="difficulty[]" value="easy" 
                                   {{ in_array('easy', request('difficulty', [])) ? 'checked' : '' }}>
                            <span>Dễ</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="difficulty[]" value="medium"
                                   {{ in_array('medium', request('difficulty', [])) ? 'checked' : '' }}>
                            <span>Trung bình</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="difficulty[]" value="hard"
                                   {{ in_array('hard', request('difficulty', [])) ? 'checked' : '' }}>
                            <span>Khó</span>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="filter-buttons">
                    <button type="submit" class="btn-apply">Áp dụng</button>
                    <a href="{{ route('tours.index') }}" class="btn-reset">Đặt lại</a>
                </div>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="tours-main">
            <div class="tours-main-header">
                <div>
                    <h1 class="tours-page-title">Danh sách Tour leo núi</h1>
                    <p class="tours-page-subtitle">Khám phá những cung đường hùng vĩ</p>
                </div>
                <div class="tours-result-info">
                    {{ $tours->total() }} kết quả
                </div>
            </div>

            @if($tours->count() > 0)
                <div class="tours-grid-modern">
                    @foreach($tours as $tour)
                        <a href="{{ route('tours.show', $tour) }}" class="tour-card-compact-link">
                            <div class="tour-card-compact" data-tour-id="{{ $tour->id }}">
                                <div class="tour-card-image-compact">
                                    @if($tour->image)
                                        <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80" alt="{{ $tour->name }}">
                                    @endif
                                    
                                    <!-- Badge độ khó -->
                                    <span class="tour-badge-difficulty tour-badge-{{ $tour->difficulty }}">
                                        {{ $tour->difficulty_text }}
                                    </span>

                                    <!-- Nút wishlist -->
                                    @auth
                                    <button class="tour-wishlist-btn" data-tour-id="{{ $tour->id }}" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist({{ $tour->id }});">
                                        <i class="bi bi-heart{{ auth()->user()->wishlists->contains('tour_id', $tour->id) ? '-fill' : '' }}"></i>
                                    </button>
                                    @endauth
                                </div>

                                <div class="tour-card-content-compact">
                                    <h3 class="tour-card-name">{{ $tour->name }}</h3>
                                    
                                    <!-- Mô tả ngắn -->
                                    @if($tour->description)
                                    <p class="tour-card-description">
                                        {{ Str::limit(strip_tags($tour->description), 100) }}
                                    </p>
                                    @endif
                                    
                                    <!-- Thông tin tour -->
                                    <div class="tour-card-meta">
                                        <div class="tour-meta-item">
                                            <i class="bi bi-geo-alt"></i>
                                            <span>{{ $tour->location ?? 'Việt Nam' }}</span>
                                        </div>
                                        
                                        <!-- Giá -->
                                        @php
                                            $activeSchedulesWithPrice = $tour->schedules()
                                                ->where('is_active', true)
                                                ->where('departure_date', '>=', now())
                                                ->where('price', '>', 0)
                                                ->get();
                                            
                                            $minPrice = $activeSchedulesWithPrice->min('price');
                                            $maxPrice = $activeSchedulesWithPrice->max('price');
                                        @endphp
                                        
                                        @if($minPrice && $maxPrice)
                                            <div class="tour-meta-item tour-meta-price">
                                                <i class="bi bi-tag"></i>
                                                @if($minPrice == $maxPrice)
                                                    <span>{{ number_format($minPrice, 0, ',', '.') }}₫</span>
                                                @else
                                                    <span>{{ number_format($minPrice, 0, ',', '.') }}₫ - {{ number_format($maxPrice, 0, ',', '.') }}₫</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Đánh giá và tương tác -->
                                    <div class="tour-card-stats">
                                        @php
                                            $avgRating = $tour->reviews()->where('status', 'approved')->avg('rating') ?? 0;
                                            $reviewCount = $tour->reviews()->where('status', 'approved')->count();
                                            $wishlistCount = $tour->wishlists()->count();
                                        @endphp
                                        
                                        <!-- Rating -->
                                        <div class="tour-stat-item">
                                            @if($reviewCount > 0)
                                                <div class="rating-stars-compact">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= floor($avgRating))
                                                            <i class="bi bi-star-fill"></i>
                                                        @elseif($i - 0.5 <= $avgRating)
                                                            <i class="bi bi-star-half"></i>
                                                        @else
                                                            <i class="bi bi-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="stat-text">{{ number_format($avgRating, 1) }} ({{ $reviewCount }})</span>
                                            @else
                                                <div class="rating-stars-compact rating-empty">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star"></i>
                                                    @endfor
                                                </div>
                                                <span class="stat-text text-muted">Chưa có đánh giá</span>
                                            @endif
                                        </div>

                                        <!-- Wishlist count -->
                                        <div class="tour-stat-item wishlist-count-stat" style="{{ $wishlistCount > 0 ? '' : 'display: none;' }}">
                                            <i class="bi bi-heart-fill text-danger"></i>
                                            <span class="stat-text"><span class="wishlist-count-number">{{ $wishlistCount }}</span> yêu thích</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="tours-pagination">
                    {{ $tours->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="tours-empty">
                    <i class="bi bi-inbox"></i>
                    <h4>Không tìm thấy tour nào</h4>
                    <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                    <a href="{{ route('tours.index') }}" class="btn-apply">
                        Xem tất cả tour
                    </a>
                </div>
            @endif
        </main>
    </div>
</div>

<style>
/* Tours Page Modern Layout */
.tours-page-modern {
    background: #f5f5f5;
    min-height: 100vh;
    padding: 80px 0 60px;
}

.tours-page-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 32px;
}

/* Sidebar Styles */
.tours-sidebar {
    background: white;
    border-radius: 12px;
    padding: 24px;
    height: fit-content;
    position: sticky;
    top: 100px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.sidebar-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 24px 0;
}

.sidebar-filter-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.filter-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.filter-input {
    padding: 10px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.filter-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.filter-price-range {
    display: flex;
    gap: 8px;
    align-items: center;
}

.filter-price-range .filter-input {
    flex: 1;
    min-width: 0;
}

.price-separator {
    color: #999;
    font-weight: 500;
    flex-shrink: 0;
}

.filter-checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
}

.filter-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.filter-checkbox span {
    font-size: 14px;
    color: #555;
}

.filter-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 8px;
}

.btn-apply {
    padding: 12px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: block;
}

.btn-apply:hover {
    background: #059669;
    transform: translateY(-1px);
}

.btn-reset {
    padding: 12px;
    background: white;
    color: #666;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: block;
}

.btn-reset:hover {
    background: #f5f5f5;
    border-color: #d0d0d0;
}

/* Main Content Styles */
.tours-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.tours-main-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.tours-page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.tours-page-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0;
}

.tours-result-info {
    font-size: 14px;
    color: #999;
    padding-top: 4px;
}

/* Tours Grid */
.tours-grid-modern {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

/* Tour Card Compact */
.tour-card-compact-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.tour-card-compact {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    height: 100%;
    cursor: pointer;
}

.tour-card-compact:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.tour-card-image-compact {
    position: relative;
    height: 200px;
    overflow: hidden;
    flex-shrink: 0;
}

.tour-card-image-compact img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.tour-card-compact:hover .tour-card-image-compact img {
    transform: scale(1.05);
}

.tour-badge-difficulty {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    color: white;
}

.tour-badge-easy {
    background: #10b981;
}

.tour-badge-medium {
    background: #f59e0b;
}

.tour-badge-hard {
    background: #ef4444;
}

.tour-card-content-compact {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.tour-card-name {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    line-height: 1.4;
    min-height: 44px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tour-card-description {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    margin: 8px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Tour Meta Info */
.tour-card-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-bottom: 0px;
    margin-bottom: 5px;
}

.tour-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #555;
}

.tour-meta-item i {
    font-size: 14px;
    color: #10b981;
    width: 16px;
}

.tour-meta-price {
    color: #ef4444;
    font-weight: 600;
    font-size: 14px;
}

.tour-meta-price i {
    color: #ef4444;
}

/* Tour Stats (Rating & Wishlist) */
.tour-card-stats {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tour-stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.rating-stars-compact {
    display: flex;
    gap: 2px;
}

.rating-stars-compact i {
    font-size: 13px;
    color: #fbbf24;
}

.rating-stars-compact.rating-empty i {
    color: #d1d5db;
}

.stat-text {
    font-size: 13px;
    color: #666;
    font-weight: 500;
}

.tour-stat-item > i {
    font-size: 14px;
}

/* Wishlist Button */
.tour-wishlist-btn {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 10;
}

.tour-wishlist-btn:hover {
    background: white;
    transform: scale(1.1);
}

.tour-wishlist-btn i {
    font-size: 18px;
    color: #ef4444;
}

/* Pagination */
.tours-pagination {
    display: flex;
    justify-content: center;
    margin-top: 32px;
}

/* Empty State */
.tours-empty {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 12px;
}

.tours-empty i {
    font-size: 64px;
    color: #ddd;
}

.tours-empty h4 {
    margin-top: 20px;
    color: #333;
    font-size: 20px;
}

.tours-empty p {
    color: #999;
    margin-bottom: 24px;
}

/* Responsive */
@media (max-width: 1024px) {
    .tours-page-container {
        grid-template-columns: 1fr;
    }
    
    .tours-sidebar {
        position: relative;
        top: 0;
    }
    
    .tours-grid-modern {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .tours-grid-modern {
        grid-template-columns: 1fr;
    }
    
    .tours-page-title {
        font-size: 24px;
    }
    
    .tours-main-header {
        flex-direction: column;
        gap: 12px;
    }
}
</style>
@endsection

@push('scripts')
<script>
// Wishlist toggle function
function toggleWishlist(tourId) {
    @auth
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
        if (data.success) {
            const icon = document.querySelector(`.tour-wishlist-btn[data-tour-id="${tourId}"] i`);
            const card = document.querySelector(`.tour-card-compact[data-tour-id="${tourId}"]`);
            const wishlistCountStat = card?.querySelector('.wishlist-count-stat');
            const wishlistCountNumber = wishlistCountStat?.querySelector('.wishlist-count-number');
            
            if (data.action === 'added') {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                Toast.success('❤️ Đã thêm vào yêu thích!');
                
                // Update wishlist count on card
                if (wishlistCountStat && wishlistCountNumber) {
                    const currentCount = parseInt(wishlistCountNumber.textContent) || 0;
                    wishlistCountNumber.textContent = currentCount + 1;
                    wishlistCountStat.style.display = 'flex';
                }
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                Toast.info('Đã xóa khỏi yêu thích');
                
                // Update wishlist count on card
                if (wishlistCountStat && wishlistCountNumber) {
                    const currentCount = parseInt(wishlistCountNumber.textContent);
                    const newCount = Math.max(0, currentCount - 1);
                    wishlistCountNumber.textContent = newCount;
                    
                    if (newCount === 0) {
                        wishlistCountStat.style.display = 'none';
                    }
                }
            }
            
            // Update counter in header if exists
            const wishlistCounter = document.querySelector('.wishlist-count');
            if (wishlistCounter && data.count !== undefined) {
                wishlistCounter.textContent = data.count;
                wishlistCounter.style.display = data.count > 0 ? 'flex' : 'none';
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        Toast.error('Có lỗi xảy ra!');
    });
    @else
    Toast.info('🔒 Vui lòng đăng nhập để lưu yêu thích!');
    setTimeout(() => {
        window.location.href = '{{ route("login") }}';
    }, 1500);
    @endauth
}

</script>
@endpush
