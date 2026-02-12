@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<!-- Hero Section -->
<div class="hero-simple">
    <div class="hero-simple-bg">
        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&q=80" alt="Mountains">
    </div>
    <div class="hero-simple-content">
        <h1>Chinh Phục Đỉnh Cao,<br>Khám Phá Giới Hạn</h1>
        <p>Trải nghiệm leo núi chuyên nghiệp cùng đội ngũ hướng dẫn viên giàu kinh nghiệm</p>
        <div class="hero-search">
            <form action="{{ route('tours.index') }}" method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm tour..." value="{{ request('search') }}">
                <button type="submit">
                    <i class="bi bi-search"></i> Tìm kiếm
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Destinations Section -->
<section class="destinations-section">
    <div class="container-destinations">
        <div class="section-header-simple">
            <h2>Các Địa Điểm Nổi Bật</h2>
            <p>Khám phá những cung đường leo núi hấp dẫn</p>
        </div>

        @if($tours->count() > 0)
            <div class="destinations-grid">
                @foreach($tours->take(6) as $tour)
                    <div class="destination-card">
                        <div class="destination-image">
                            @if($tour->image)
                                <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&q=80" alt="{{ $tour->name }}">
                            @endif
                            <div class="destination-badge destination-{{ $tour->difficulty }}">
                                {{ $tour->difficulty_text }}
                            </div>
                        </div>
                        <div class="destination-content">
                            <h3>{{ $tour->name }}</h3>
                            <p class="destination-location">
                                <i class="bi bi-geo-alt"></i> {{ $tour->location }}
                            </p>
                            <div class="destination-footer">
                                <span class="destination-price">
                                    @php
                                        $activeSchedulesPrice = $tour->schedules()
                                            ->where('is_active', true)
                                            ->where('departure_date', '>=', now())
                                            ->where('price', '>', 0)
                                            ->get();
                                        $minPrice = $activeSchedulesPrice->min('price');
                                        $maxPrice = $activeSchedulesPrice->max('price');
                                    @endphp
                                    @if($minPrice && $maxPrice && $minPrice != $maxPrice)
                                        {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} VND
                                    @elseif($minPrice)
                                        {{ number_format($minPrice, 0, ',', '.') }} VND
                                    @else
                                        <span class="text-muted">Liên hệ</span>
                                    @endif
                                </span>
                                <a href="{{ route('tours.show', $tour) }}" class="btn-destination">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($tours->count() > 6)
                <div class="view-all-wrapper">
                    <a href="{{ route('tours.index') }}" class="btn-view-all">
                        Xem tất cả <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            @endif
        @else
            <div class="empty-tours">
                <i class="bi bi-inbox"></i>
                <p>Chưa có tour nào. Vui lòng quay lại sau!</p>
            </div>
        @endif
    </div>
</section>

<style>
/* Hero Simple */
.hero-simple {
    position: relative;
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.hero-simple-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-simple-bg::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.5));
}

.hero-simple-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-simple-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
    max-width: 800px;
    padding: 0 24px;
}

.hero-simple-content h1 {
    font-size: 56px;
    font-weight: 800;
    margin-bottom: 24px;
    line-height: 1.2;
}

.hero-simple-content p {
    font-size: 20px;
    margin-bottom: 40px;
    opacity: 0.95;
}

.hero-search form {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
    background: white;
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.hero-search input {
    flex: 1;
    padding: 18px 32px;
    border: none;
    font-size: 16px;
    outline: none;
}

.hero-search button {
    padding: 18px 40px;
    background: #10b981;
    color: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
}

.hero-search button:hover {
    background: #059669;
}

/* Destinations Section */
.destinations-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.container-destinations {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

.section-header-simple {
    text-align: center;
    margin-bottom: 60px;
}

.section-header-simple h2 {
    font-size: 40px;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 16px;
}

.section-header-simple p {
    font-size: 18px;
    color: #666;
}

.destinations-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-bottom: 48px;
}

/* Destination Card */
.destination-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.destination-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
}

.destination-image {
    position: relative;
    height: 240px;
    overflow: hidden;
}

.destination-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.destination-card:hover .destination-image img {
    transform: scale(1.1);
}

.destination-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 8px 16px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 700;
    color: white;
}

.destination-easy {
    background: #10b981;
}

.destination-medium {
    background: #f59e0b;
}

.destination-hard {
    background: #ef4444;
}

.destination-content {
    padding: 24px;
}

.destination-content h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.destination-location {
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.destination-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.destination-price {
    font-size: 18px;
    font-weight: 700;
    color: #ef4444;
}

.btn-destination {
    padding: 10px 24px;
    background: #10b981;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-destination:hover {
    background: #059669;
    color: white;
    transform: translateY(-2px);
}

.view-all-wrapper {
    text-align: center;
    margin-top: 48px;
}

.btn-view-all {
    display: inline-flex;
    align-items: center;
    padding: 16px 40px;
    background: #10b981;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s;
}

.btn-view-all:hover {
    background: #059669;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.empty-tours {
    text-align: center;
    padding: 80px 20px;
}

.empty-tours i {
    font-size: 64px;
    color: #ddd;
}

.empty-tours p {
    margin-top: 20px;
    color: #999;
}

/* Responsive */
@media (max-width: 1024px) {
    .destinations-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .hero-simple {
        height: 500px;
    }
    
    .hero-simple-content h1 {
        font-size: 36px;
    }
    
    .hero-simple-content p {
        font-size: 16px;
    }
    
    .hero-search form {
        flex-direction: column;
        border-radius: 16px;
    }
    
    .hero-search button {
        border-radius: 0 0 16px 16px;
    }
    
    .destinations-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header-simple h2 {
        font-size: 32px;
    }
}
</style>
@endsection