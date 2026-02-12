@extends('layouts.app')

@section('title', $tour->name)

@section('content')
<!-- Breadcrumb -->
<div class="tour-breadcrumb-container">
    <div class="container">
        <nav class="simple-breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <i class="bi bi-chevron-right"></i>
            <a href="{{ route('tours.index') }}">Tours</a>
            <i class="bi bi-chevron-right"></i>
            <span>{{ $tour->name }}</span>
        </nav>
    </div>
</div>


<!-- Main Tabs -->
<!-- REMOVED: Tab navigation, replaced with 2-column layout -->

<!-- Tab Contents -->
<div class="tour-content-wrapper">
    <div class="container">
        <div class="tour-two-column-layout">
            <!-- LEFT COLUMN: Gallery & Info -->
            <div class="tour-left-column">
                <!-- Gallery Grid -->
                <div class="gallery-grid">
                    <div class="gallery-main">
                        @if($tour->image)
                        <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}">
                        @else
                        <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=1200" alt="Tour">
                        @endif
                    </div>
                    @if($tour->gallery && count($tour->gallery) > 0)
                    <div class="gallery-thumbs">
                        @foreach(array_slice($tour->gallery, 0, 4) as $index => $image)
                        <div class="gallery-thumb" onclick="openGalleryModal({{ $index }})">
                            <img src="{{ asset($image) }}" alt="Gallery {{ $index + 1 }}">
                            @if($index === 3 && count($tour->gallery) > 4)
                            <div class="gallery-more">
                                <i class="bi bi-plus-lg"></i>
                                <span>{{ count($tour->gallery) - 4 }} ảnh</span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <!-- END Gallery Grid -->

                <!-- Info Tabs -->
                <div class="info-tabs-wrapper">
                <ul class="info-tabs" role="tablist">
                    <li><button class="info-tab-btn active" data-info-tab="overview"><i class="bi bi-info-circle"></i> Tổng quan</button></li>
                    <li><button class="info-tab-btn" data-info-tab="itinerary"><i class="bi bi-calendar-week"></i> Lịch trình</button></li>
                    <li><button class="info-tab-btn" data-info-tab="includes"><i class="bi bi-list-check"></i> Bao gồm & Không bao gồm</button></li>
                    <li><button class="info-tab-btn" data-info-tab="reviews"><i class="bi bi-star"></i> Đánh giá</button></li>
                </ul>

                <!-- Info Tab Contents -->
                <div class="info-tab-contents">
                    <!-- Overview -->
                    <div class="info-tab-content active" id="info-tab-overview">
                        @if($tour->highlights_list && count($tour->highlights_list) > 0)
                        <div class="content-section">
                            <h3><i class="bi bi-stars"></i> Điểm nổi bật</h3>
                            <ul class="highlights-list">
                                @foreach($tour->highlights_list as $highlight)
                                <li><i class="bi bi-check-circle"></i> {{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="content-section">
                            <h3><i class="bi bi-info-circle"></i> Chi tiết tour</h3>
                            <div class="text-content">
                                @if($tour->overview)
                                    {!! nl2br(e($tour->overview)) !!}
                                @elseif($tour->description)
                                    {!! nl2br(e($tour->description)) !!}
                                @else
                                    <p>Chưa có thông tin chi tiết.</p>
                                @endif
                            </div>
                        </div>

                        @if($tour->map_lat && $tour->map_lng)
                        <div class="content-section">
                            <h3><i class="bi bi-map"></i> Vị trí</h3>
                            <div class="map-wrapper">
                                <iframe 
                                    src="https://www.google.com/maps?q={{ $tour->map_lat }},{{ $tour->map_lng }}&output=embed"
                                    width="100%" 
                                    height="400" 
                                    frameborder="0"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Itinerary -->
                    <div class="info-tab-content" id="info-tab-itinerary">
                        @php
                            // Parse itinerary
                            $itinerary = is_string($tour->itinerary) ? json_decode($tour->itinerary, true) : $tour->itinerary;
                            $itineraryArray = is_array($itinerary) ? $itinerary : [];
                            
                            // Get all unique durations from schedules
                            $allDurations = $schedules->map(function($schedule) {
                                return $schedule->end_date 
                                    ? \Carbon\Carbon::parse($schedule->departure_date)->diffInDays(\Carbon\Carbon::parse($schedule->end_date)) + 1
                                    : 1;
                            })->unique()->sort()->values();
                            
                            // If no schedules, use tour default duration
                            if ($allDurations->isEmpty()) {
                                $allDurations = collect([$tour->duration_days]);
                            }
                        @endphp
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Lịch trình linh hoạt theo thời gian</strong><br>
                                <small class="text-muted">Tour này có {{ $allDurations->count() }} tùy chọn thời gian khác nhau. Mỗi chuyến đi sẽ có trải nghiệm phù hợp với số ngày bạn chọn.</small>
                            </div>
                        </div>

                        <!-- Display all itinerary variants -->
                        <div class="itinerary-variants">
                        @foreach($allDurations as $duration)
                            @php
                                // Generate itinerary for this duration
                                $dynamicItinerary = [];
                                
                                if ($duration == 1) {
                                    // Tour 1 ngày
                                    $dynamicItinerary = [
                                        'Sáng: Tập trung và khởi hành. Bắt đầu hành trình chinh phục đỉnh núi. Nghỉ ngơi và thưởng thức cảnh quan thiên nhiên.',
                                        'Trưa: Dừng chân ăn trưa và nghỉ ngơi. Tiếp tục leo núi với tốc độ phù hợp.',
                                        'Chiều: Hoàn thành hành trình và trở về điểm xuất phát. Chia sẻ trải nghiệm và kết thúc tour.'
                                    ];
                                } elseif ($duration <= count($itineraryArray)) {
                                    // Lấy đúng số ngày từ itinerary gốc
                                    $dynamicItinerary = array_slice($itineraryArray, 0, $duration);
                                } elseif (count($itineraryArray) > 0) {
                                    // Nếu duration dài hơn itinerary, thêm ngày nghỉ
                                    $dynamicItinerary = $itineraryArray;
                                    $extraDays = $duration - count($itineraryArray);
                                    for ($i = 0; $i < $extraDays; $i++) {
                                        $dayNum = count($itineraryArray) + $i + 1;
                                        $dynamicItinerary[] = "Ngày {$dayNum}: Nghỉ dưỡng tự do - Khám phá địa phương hoặc tham gia hoạt động bổ sung theo sở thích.";
                                    }
                                } else {
                                    // Không có itinerary, tạo mẫu chung
                                    for ($i = 0; $i < $duration; $i++) {
                                        $dayNum = $i + 1;
                                        if ($dayNum == 1) {
                                            $dynamicItinerary[] = "Ngày 1: Khởi hành - Bắt đầu hành trình trekking - Dựng trại ban đêm";
                                        } elseif ($dayNum == $duration) {
                                            $dynamicItinerary[] = "Ngày {$dayNum}: Chinh phục đỉnh - Ngắm cảnh toàn vùng - Xuống núi - Trở về";
                                        } else {
                                            $dynamicItinerary[] = "Ngày {$dayNum}: Tiếp tục hành trình - Khám phá cảnh quan thiên nhiên - Cắm trại";
                                        }
                                    }
                                }
                                
                                // Count how many schedules have this duration
                                $scheduleCount = $schedules->filter(function($s) use ($duration) {
                                    $days = $s->end_date 
                                        ? \Carbon\Carbon::parse($s->departure_date)->diffInDays(\Carbon\Carbon::parse($s->end_date)) + 1
                                        : 1;
                                    return $days == $duration;
                                })->count();
                                
                                $nights = $duration - 1;
                            @endphp
                            
                            @if(count($dynamicItinerary) > 0)
                            <div class="itinerary-variant-section" id="itinerary-{{ $duration }}-days">
                                <div class="variant-header">
                                    <div>
                                        <h3>
                                            <i class="bi bi-calendar-week"></i> 
                                            Lịch trình {{ $duration }} ngày{{ $nights > 0 ? " {$nights} đêm" : '' }}
                                            @if($duration == $tour->duration_days)
                                            <span class="badge bg-success ms-2">Chuẩn</span>
                                            @endif
                                        </h3>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-calendar-check"></i> 
                                            Có {{ $scheduleCount }} lịch khởi hành với thời gian này
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="itinerary">
                                    @if($duration == 1)
                                        {{-- Special format for 1-day trips --}}
                                        @foreach($dynamicItinerary as $index => $activity)
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                @if($index == 0)
                                                <i class="bi bi-sunrise"></i>
                                                @elseif($index == 1)
                                                <i class="bi bi-sun"></i>
                                                @else
                                                <i class="bi bi-sunset"></i>
                                                @endif
                                            </div>
                                            <div class="day-content">
                                                <h4>{{ ['Buổi sáng', 'Buổi trưa', 'Buổi chiều'][$index] }}</h4>
                                                <p>{{ $activity }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        {{-- Multi-day format --}}
                                        @foreach($dynamicItinerary as $index => $day)
                                        <div class="timeline-item">
                                            <div class="timeline-marker">{{ $index + 1 }}</div>
                                            <div class="day-content">
                                                <h4>{{ is_array($day) ? ($day['day'] ?? 'Ngày '.($index + 1)) : 'Ngày '.($index + 1) }}</h4>
                                                <p>{!! nl2br(e(is_array($day) ? ($day['content'] ?? $day) : $day)) !!}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                
                                @if($duration > $tour->duration_days)
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle"></i>
                                    Chuyến đi này kéo dài hơn tour chuẩn {{ $tour->duration_days }} ngày. Các ngày bổ sung giúp bạn có thêm thời gian nghỉ ngơi và khám phá sâu hơn.
                                </div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                        </div>
                    </div>

                    <!-- Includes/Excludes -->
                    <div class="info-tab-content" id="info-tab-includes">
                        <div class="content-section">
                            <div class="two-col-list">
                                <div>
                                    <h3 class="green"><i class="bi bi-check-circle"></i> Bao gồm</h3>
                                    @if($tour->includes_list && count($tour->includes_list) > 0)
                                    <ul class="check-list">
                                        @foreach($tour->includes_list as $item)
                                        <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted">Chưa cập nhật</p>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="red"><i class="bi bi-x-circle"></i> Không bao gồm</h3>
                                    @if($tour->excludes_list && count($tour->excludes_list) > 0)
                                    <ul class="x-list">
                                        @foreach($tour->excludes_list as $item)
                                        <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted">Chưa cập nhật</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="info-tab-content" id="info-tab-reviews">
                        @if($tour->reviews_count > 0)
                        <div class="content-section">
                            <h3><i class="bi bi-star"></i> Đánh giá từ khách hàng</h3>
                            <div class="rating-summary">
                                <div class="rating-big">{{ number_format($tour->average_rating, 1) }}</div>
                                <div class="rating-info">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= round($tour->average_rating) ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <p>{{ $tour->reviews_count }} đánh giá</p>
                                </div>
                            </div>
                            <div class="reviews">
                                @foreach($tour->approvedReviews()->latest()->take(5)->get() as $review)
                                <div class="review">
                                    <div class="review-head">
                                        <div class="review-user">
                                            <div class="user-avatar"><i class="bi bi-person-circle"></i></div>
                                            <div>
                                                <strong>{{ $review->user->name }}</strong>
                                                <div class="review-stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p>{{ $review->comment }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="bi bi-star"></i>
                            <p>Chưa có đánh giá nào</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- END tour-left-column -->

        <!-- RIGHT COLUMN: Booking & Schedules -->
        <div class="tour-right-column">
            <div class="booking-sticky-wrapper">
                <!-- Tour Title Info -->
                <div class="tour-title-info">
                    <span class="difficulty-tag-compact">{{ $tour->difficulty_text }}</span>
                    <h2>{{ $tour->name }}</h2>
                    <div class="tour-location-compact"><i class="bi bi-geo-alt"></i> {{ $tour->location }}</div>
                    <div class="tour-price-text">
                        <span class="price-label-text">Giá tour:</span>
                        @php
                            // Lấy giá từ các schedules đang hoạt động
                            $activeSchedulesWithPrice = $schedules->where('price', '>', 0);
                            $minPrice = $activeSchedulesWithPrice->min('price');
                            $maxPrice = $activeSchedulesWithPrice->max('price');
                        @endphp
                        @if($minPrice && $maxPrice)
                            @if($minPrice == $maxPrice)
                                <span class="price-value-text">{{ number_format($minPrice) }}₫</span>
                            @else
                                <span class="price-value-text">{{ number_format($minPrice) }}₫ - {{ number_format($maxPrice) }}₫</span>
                            @endif
                        @else
                            <span class="price-value-text text-muted">Liên hệ</span>
                        @endif
                        <span class="price-note-text">/ người</span>
                    </div>
                    <div class="tour-meta-compact">
                        @if($tour->reviews_count > 0)
                        <span><i class="bi bi-star-fill"></i> {{ number_format($tour->average_rating, 1) }} ({{ $tour->reviews_count }})</span>
                        @endif
                    </div>
                    @auth
                    <button type="button" class="btn-wishlist-compact {{ auth()->user()->wishlists()->where('tour_id', $tour->id)->exists() ? 'active' : '' }}" id="wishlistBtn" data-tour-id="{{ $tour->id }}">
                        <i class="bi {{ auth()->user()->wishlists()->where('tour_id', $tour->id)->exists() ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                        <span>Yêu thích</span>
                    </button>
                    @else
                    <a href="{{ route('login') }}" class="btn-wishlist-compact">
                        <i class="bi bi-heart"></i>
                        <span>Yêu thích</span>
                    </a>
                    @endauth
                </div>

                <div class="booking-divider"></div>

                <div class="schedule-section-header">
                    <h3><i class="bi bi-calendar-event"></i> Lịch khởi hành</h3>
                    <p class="schedule-note">
                        <i class="bi bi-info-circle"></i> 
                        Tour {{ $tour->duration_days }} ngày. Chọn chuyến đi phù hợp với thời gian của bạn.
                    </p>
                </div>

                @if($schedules->count() > 0)
                <div class="schedule-list">
                    @foreach($schedules as $schedule)
                    @php
                        $days = $schedule->end_date 
                            ? \Carbon\Carbon::parse($schedule->departure_date)->diffInDays(\Carbon\Carbon::parse($schedule->end_date)) + 1 
                            : 1;
                        $nights = $days - 1;
                        $isDifferent = $days != $tour->duration_days;
                    @endphp
                    <div class="schedule-item {{ $selectedSchedule && $selectedSchedule->id == $schedule->id ? 'selected' : '' }}">
                        <div class="schedule-date-row">
                            <div class="schedule-date-info">
                                <i class="bi bi-calendar-check"></i>
                                <div class="date-text">
                                    <span class="date-range">
                                        {{ \Carbon\Carbon::parse($schedule->departure_date)->format('d/m/Y') }}
                                        @if($schedule->end_date)
                                        - {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}
                                        @endif
                                    </span>
                                    <span class="duration-text">
                                        {{ $days }} ngày{{ $nights > 0 ? " {$nights} đêm" : '' }}
                                        @if($isDifferent)
                                        <span class="badge bg-warning text-dark ms-1" 
                                              title="Chuyến đi {{ $days > $tour->duration_days ? 'kéo dài' : 'rút gọn' }} so với tour chuẩn {{ $tour->duration_days }} ngày"
                                              style="font-size: 10px;">
                                            @if($days > $tour->duration_days)
                                                <i class="bi bi-plus-circle"></i> Mở rộng
                                            @else
                                                <i class="bi bi-dash-circle"></i> Rút gọn
                                            @endif
                                        </span>
                                        @endif
                                    </span>
                                    @if($schedule->price > 0)
                                    <span class="schedule-price-text">
                                        <i class="bi bi-tag"></i> {{ number_format($schedule->price, 0, ',', '.') }}₫/người
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="schedule-status-col">
                                @if($schedule->available_slots > 0)
                                <span class="slots available">
                                    <i class="bi bi-check-circle"></i> Còn {{ $schedule->available_slots }} chỗ
                                </span>
                                @else
                                <span class="slots full">
                                    <i class="bi bi-x-circle"></i> Hết chỗ
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        @php
                            $daysUntilDeadline = $schedule->days_until_registration_deadline;
                            $isRegistrationClosed = $schedule->is_registration_closed;
                        @endphp
                        
                        @if(!$isRegistrationClosed)
                        <div class="registration-deadline-info">
                            @if($daysUntilDeadline <= 3)
                            <div class="deadline-warning urgent">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>
                                    @if($daysUntilDeadline == 0)
                                        Hôm nay là ngày cuối đăng ký!
                                    @elseif($daysUntilDeadline == 1)
                                        Còn 1 ngày để đăng ký
                                    @else
                                        Còn {{ $daysUntilDeadline }} ngày để đăng ký
                                    @endif
                                </span>
                            </div>
                            @elseif($daysUntilDeadline <= 7)
                            <div class="deadline-warning moderate">
                                <i class="bi bi-clock-history"></i>
                                <span>Còn {{ $daysUntilDeadline }} ngày để đăng ký</span>
                            </div>
                            @else
                            <div class="deadline-info normal">
                                <i class="bi bi-info-circle"></i>
                                <span>Hạn đăng ký: {{ $schedule->registration_deadline->format('d/m/Y') }}</span>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="registration-deadline-info">
                            <div class="deadline-closed">
                                <i class="bi bi-x-circle-fill"></i>
                                <span>Đã hết hạn đăng ký</span>
                            </div>
                        </div>
                        @endif
                        
                        @if($schedule->available_slots > 0 && !$isRegistrationClosed)
                        <a href="{{ route('bookings.create', ['tour' => $tour->id, 'schedule' => $schedule->id]) }}" class="btn-book-schedule">
                            <i class="bi bi-arrow-right-circle"></i> Đặt ngay
                        </a>
                        @elseif($isRegistrationClosed)
                        <button class="btn-book-schedule disabled" disabled>
                            <i class="bi bi-clock-history"></i> Đã hết hạn đăng ký
                        </button>
                        @else
                        <button class="btn-book-schedule disabled" disabled>
                            <i class="bi bi-x-circle"></i> Đã hết chỗ
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p>Hiện chưa có lịch trình khởi hành</p>
                    <p class="text-muted">Vui lòng liên hệ để biết thêm chi tiết</p>
                </div>
                @endif

                <div class="booking-contact">
                    <h4><i class="bi bi-headset"></i> Cần hỗ trợ?</h4>
                    <p><i class="bi bi-telephone"></i> <a href="tel:0123456789">0123 456 789</a></p>
                    <p><i class="bi bi-envelope"></i> <a href="mailto:info@mountain.vn">info@mountain.vn</a></p>
                </div>
            </div>
        </div>
        <!-- END tour-right-column -->

    </div>
    <!-- END tour-two-column-layout -->
    </div>
    <!-- END container -->
</div>
<!-- END tour-content-wrapper -->

<!-- Gallery Modal -->
@if($tour->gallery && count($tour->gallery) > 0)
<div class="gallery-modal" id="galleryModal" onclick="if(event.target === this) closeGalleryModal()">
    <button class="modal-close" onclick="closeGalleryModal()"><i class="bi bi-x-lg"></i></button>
    <button class="modal-nav prev" onclick="prevImage()"><i class="bi bi-chevron-left"></i></button>
    <button class="modal-nav next" onclick="nextImage()"><i class="bi bi-chevron-right"></i></button>
    <div class="modal-container">
        <img src="" id="modalMainImage" alt="Gallery">
        <div class="modal-counter"><span id="currentIndex">1</span> / {{ count($tour->gallery) }}</div>
    </div>
</div>
@endif

@push('styles')
<style>
/* Base */
* { box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1e293b; line-height: 1.6; }
.container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }

/* Breadcrumb */
.tour-breadcrumb-container { background: #f8fafc; padding: 16px 0; }
.simple-breadcrumb { list-style: none; display: flex; gap: 8px; align-items: center; margin: 0; padding: 0; }
.simple-breadcrumb a { color: #64748b; text-decoration: none; }
.simple-breadcrumb a:hover { color: #10b981; }
.simple-breadcrumb span { color: #1e293b; font-weight: 500; }

/* Title Section */
.tour-title-section { padding: 32px 0; background: white; }
.title-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
.difficulty-tag { 
    display: inline-block; 
    padding: 6px 14px; 
    background: #10b981; 
    color: white; 
    border-radius: 20px; 
    font-size: 13px; 
    font-weight: 600; 
    margin-bottom: 12px; 
}
.title-col h1 { font-size: 32px; margin: 0 0 8px 0; font-weight: 700; }
.tour-location { color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
.tour-meta { display: flex; gap: 20px; font-size: 14px; color: #64748b; }
.tour-meta span { display: flex; align-items: center; gap: 6px; }
.tour-meta i { color: #10b981; }
.btn-wishlist { 
    background: white; 
    border: 2px solid #e2e8f0; 
    padding: 12px 24px; 
    border-radius: 8px; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    font-weight: 600; 
    transition: all 0.2s; 
}
.btn-wishlist:hover { border-color: #ef4444; color: #ef4444; }
.btn-wishlist.active { background: #ef4444; border-color: #ef4444; color: white; }

/* Two Column Layout */
.tour-content-wrapper { padding: 48px 0; background: #f8fafc; }
.tour-two-column-layout {
    display: grid !important;
    grid-template-columns: 1fr 420px !important;
    gap: 40px;
    align-items: start;
}

@media (min-width: 993px) {
    .tour-two-column-layout {
        grid-template-columns: 1fr 420px !important;
    }
}

.tour-left-column {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    width: 100%;
    min-width: 0;
}

.tour-right-column {
    position: sticky;
    top: 100px;
    width: 100%;
    max-width: 420px;
}

/* Gallery Grid */
.gallery-grid { 
    display: block;
    margin-bottom: 0;
    border-radius: 12px 12px 0 0;
    overflow: hidden; 
}
.gallery-main { 
    height: auto;
    max-height: 600px;
    overflow: hidden; 
}
.gallery-main img { 
    width: 100%; 
    height: auto;
    max-height: 600px;
    object-fit: cover; 
    display: block;
}
.gallery-thumbs { 
    display: none;
}

/* Info Tabs */
.info-tabs-wrapper { background: white; border-radius: 12px; padding: 32px; }
.info-tabs { 
    list-style: none; 
    display: flex; 
    gap: 8px; 
    border-bottom: 2px solid #e2e8f0; 
    margin: 0 0 32px 0; 
    padding: 0; 
}
.info-tab-btn { 
    background: transparent; 
    border: none; 
    padding: 12px 20px; 
    font-size: 15px; 
    font-weight: 600; 
    cursor: pointer; 
    border-bottom: 3px solid transparent; 
    margin-bottom: -2px; 
    transition: all 0.3s; 
    color: #64748b; 
    display: flex; 
    align-items: center; 
    gap: 8px; 
}
.info-tab-btn:hover { color: #10b981; }
.info-tab-btn.active { color: #10b981; border-bottom-color: #10b981; }

.info-tab-content { display: none; }
.info-tab-content.active { display: block; }

/* Content Sections */
.content-section { margin-bottom: 32px; }
.content-section:last-child { margin-bottom: 0; }
.content-section h3 { 
    font-size: 20px; 
    margin-bottom: 20px; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    color: #1e293b; 
}
.content-section h3 i { color: #10b981; }
.content-section h3.green { color: #10b981; }
.content-section h3.red { color: #ef4444; }

/* Highlights */
.highlights-list { list-style: none; display: grid; gap: 12px; padding: 0; margin: 0; }
.highlights-list li { 
    display: flex; 
    align-items: flex-start; 
    gap: 12px; 
    padding: 12px; 
    background: #f8fafc; 
    border-radius: 8px; 
}
.highlights-list i { color: #10b981; font-size: 20px; flex-shrink: 0; margin-top: 2px; }

/* Text Content */
.text-content { color: #475569; line-height: 1.8; }

/* Itinerary */
.itinerary-variants { display: grid; gap: 40px; }
.itinerary-variant-section {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s;
}
.itinerary-variant-section:hover {
    border-color: #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
}
.variant-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}
.variant-header h3 {
    font-size: 20px;
    margin: 0 0 8px 0;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.variant-header h3 i {
    color: #10b981;
}
.variant-header p {
    font-size: 14px;
}

.itinerary { display: grid; gap: 24px; }
.timeline-item { display: flex; gap: 20px; }
.timeline-marker { 
    background: #10b981; 
    color: white; 
    width: 48px; 
    height: 48px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-weight: bold; 
    font-size: 18px; 
    flex-shrink: 0; 
}
.timeline-marker i { font-size: 20px; }
.day-content h4 { margin: 0 0 8px 0; font-size: 16px; }
.day-content p { color: #64748b; margin: 0; line-height: 1.6; }

/* Lists */
.two-col-list { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
.check-list, .x-list { list-style: none; display: grid; gap: 8px; padding: 0; margin: 0; }
.check-list li:before { content: "✓"; color: #10b981; margin-right: 8px; font-weight: bold; }
.x-list li:before { content: "×"; color: #ef4444; margin-right: 8px; font-weight: bold; }
.text-muted { color: #94a3b8; }

/* Map */
.map-wrapper { border-radius: 8px; overflow: hidden; }

/* Reviews */
.rating-summary { 
    display: flex; 
    align-items: center; 
    gap: 24px; 
    padding: 24px; 
    background: #f8fafc; 
    border-radius: 8px; 
    margin-bottom: 24px; 
}
.rating-big { font-size: 48px; font-weight: bold; color: #10b981; }
.rating-info { flex: 1; }
.rating-stars { font-size: 24px; color: #fbbf24; margin-bottom: 4px; }
.rating-info p { margin: 0; color: #64748b; }
.reviews { display: grid; gap: 20px; }
.review { padding: 20px; background: #f8fafc; border-radius: 8px; }
.review-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.review-user { display: flex; align-items: center; gap: 12px; }
.user-avatar { font-size: 32px; color: #64748b; }
.review-stars { font-size: 14px; color: #fbbf24; }
.review-date { font-size: 14px; color: #94a3b8; }
.review p { margin: 0; color: #475569; }

/* Booking Right Column */
.booking-sticky-wrapper {
    background: white;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    overflow: hidden;
}

/* Tour Title Info in Right Column */
.tour-title-info {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.tour-title-info h2 {
    font-size: 20px;
    font-weight: 700;
    margin: 8px 0;
    color: #1a1a1a;
    line-height: 1.3;
}

.difficulty-tag-compact {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    color: white;
    background: #10b981;
    text-transform: uppercase;
}

.tour-location-compact {
    color: #64748b;
    font-size: 14px;
    margin: 8px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.tour-location-compact i {
    color: #10b981;
}

.tour-price-text {
    color: #1a1a1a;
    font-size: 14px;
    margin: 8px 0;
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-weight: 500;
}

.price-label-text {
    color: #64748b;
}

.price-value-text {
    color: #10b981;
    font-size: 20px;
    font-weight: 700;
}

.price-note-text {
    color: #64748b;
    font-size: 13px;
}

.tour-meta-compact {
    display: flex;
    gap: 12px;
    font-size: 13px;
    color: #64748b;
    margin: 8px 0;
}

.tour-meta-compact span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.tour-meta-compact i {
    color: #fbbf24;
}

.btn-wishlist-compact {
    width: 100%;
    padding: 10px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    color: #64748b;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 12px;
}

.btn-wishlist-compact:hover {
    border-color: #ef4444;
    color: #ef4444;
}

.btn-wishlist-compact.active {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.btn-wishlist-compact i {
    font-size: 16px;
}

/* Tour Price Box - Inline */
.tour-price-box { 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
    color: white; 
    padding: 16px 24px; 
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-bottom: 1px solid #e2e8f0;
}
.price-label { 
    font-size: 14px; 
    opacity: 0.9; 
}
.price-value { 
    font-size: 24px; 
    font-weight: bold; 
}
.price-note { 
    font-size: 13px; 
    opacity: 0.8; 
}

.booking-divider {
    height: 8px;
    background: #f8fafc;
}

.schedule-section-header {
    padding: 16px 24px 12px;
}

.schedule-section-header h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #1a1a1a;
}

.schedule-section-header h3 i {
    color: #10b981;
}

.schedule-list { 
    padding: 0 16px 16px;
    display: grid; 
    gap: 12px;
    max-height: 400px;
    overflow-y: auto;
}

/* Schedule Item - Clean Design */
.schedule-item { 
    background: #f8fafc; 
    border: 2px solid #e2e8f0; 
    border-radius: 10px; 
    padding: 16px; 
    transition: all 0.3s; 
}
.schedule-item:hover { 
    border-color: #10b981; 
    background: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}
.schedule-date-row { 
    display: flex; 
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px; 
}
.schedule-date-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
}
.schedule-date-info i { 
    color: #10b981;
    font-size: 16px;
    flex-shrink: 0;
}
.schedule-date-info .date-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.schedule-date-info .date-range {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}
.schedule-date-info .duration-text {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
}
.schedule-date-info .schedule-price-text {
    font-size: 12px;
    font-weight: 600;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 4px;
}
.schedule-date-info .schedule-price-text i {
    font-size: 11px;
}
.slots { 
    display: inline-flex; 
    align-items: center; 
    gap: 6px; 
    padding: 5px 12px; 
    border-radius: 20px; 
    font-size: 12px; 
    font-weight: 600;
    white-space: nowrap;
}
.slots.available { background: #d1fae5; color: #059669; }
.slots.full { background: #fee2e2; color: #dc2626; }

.btn-book-schedule { 
    width: 100%;
    padding: 10px; 
    background: #10b981; 
    color: white; 
    border: none; 
    border-radius: 6px; 
    font-weight: 600; 
    font-size: 14px;
    cursor: pointer; 
    transition: all 0.3s; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 8px; 
    text-decoration: none;
}
.btn-book-schedule:hover { 
    background: #059669; 
    transform: translateY(-2px);
    color: white;
}
.btn-book-schedule.disabled { 
    background: #e2e8f0; 
    color: #94a3b8; 
    cursor: not-allowed; 
    transform: none; 
}
.btn-book-schedule.disabled:hover { transform: none; }

/* Registration Deadline Info */
.registration-deadline-info {
    margin: 12px 0;
}

.deadline-warning, .deadline-info, .deadline-closed {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

.deadline-warning.urgent {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.deadline-warning.urgent i {
    color: #dc2626;
    font-size: 14px;
    animation: pulse 2s infinite;
}

.deadline-warning.moderate {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fde68a;
}

.deadline-warning.moderate i {
    color: #d97706;
    font-size: 14px;
}

.deadline-info.normal {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.deadline-info.normal i {
    color: #1e40af;
    font-size: 14px;
}

.deadline-closed {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.deadline-closed i {
    color: #64748b;
    font-size: 14px;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.schedule-status-col {
    display: flex;
    align-items: center;
}

.booking-contact {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.booking-contact h4 { 
    font-size: 15px; 
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.booking-contact h4 i { color: #10b981; }
.booking-contact p { 
    margin: 8px 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.booking-contact a { color: #10b981; text-decoration: none; font-weight: 500; }
.booking-contact a:hover { text-decoration: underline; }
.booking-contact i { color: #64748b; } 
    transition: all 0.3s; 
}
.schedule-card:hover { border-color: #10b981; }
.schedule-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 16px; 
}
.schedule-date { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.schedule-date i { color: #10b981; }
.slots { 
    display: inline-flex; 
    align-items: center; 
    gap: 6px; 
    padding: 6px 12px; 
    border-radius: 20px; 
    font-size: 13px; 
    font-weight: 600; 
}
.slots.available { background: #d1fae5; color: #059669; }
.slots.full { background: #fee2e2; color: #dc2626; }

.btn-book-schedule { 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 8px; 
    width: 100%; 
    background: #10b981; 
    color: white; 
    border: none; 
    padding: 14px; 
    border-radius: 8px; 
    font-weight: 600; 
    text-decoration: none; 
    transition: all 0.3s; 
}
.btn-book-schedule:hover { background: #059669; color: white; }
.btn-book-schedule.disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }

.booking-contact { 
    background: white; 
    padding: 20px; 
    border-radius: 12px; 
    border: 1px solid #e2e8f0; 
    margin-top: 24px; 
}
.booking-contact h4 { font-size: 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.booking-contact h4 i { color: #10b981; }
.booking-contact p { margin: 8px 0; display: flex; align-items: center; gap: 8px; color: #475569; }
.booking-contact i { color: #10b981; }
.booking-contact a { color: #10b981; text-decoration: none; }

/* Empty State */
.empty-state { 
    text-align: center; 
    padding: 60px 20px; 
    color: #94a3b8; 
}
.empty-state i { font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.5; }
.empty-state p { margin: 8px 0; }

/* Gallery Modal */
.gallery-modal { 
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0, 0, 0, 0.95); 
    z-index: 9999; 
    align-items: center; 
    justify-content: center; 
}
.gallery-modal.active { display: flex; }
.modal-close { 
    position: absolute; 
    top: 20px; 
    right: 20px; 
    background: rgba(255, 255, 255, 0.1); 
    color: white; 
    border: none; 
    width: 48px; 
    height: 48px; 
    border-radius: 50%; 
    cursor: pointer; 
    font-size: 24px; 
    z-index: 10; 
}
.modal-nav { 
    position: absolute; 
    top: 50%; 
    transform: translateY(-50%); 
    background: rgba(255, 255, 255, 0.1); 
    color: white; 
    border: none; 
    width: 48px; 
    height: 48px; 
    border-radius: 50%; 
    cursor: pointer; 
    font-size: 24px; 
    z-index: 10; 
}
.modal-nav.prev { left: 20px; }
.modal-nav.next { right: 20px; }
.modal-container { max-width: 90%; max-height: 90%; position: relative; }
.modal-container img { max-width: 100%; max-height: 80vh; display: block; }
.modal-counter { 
    text-align: center; 
    color: white; 
    margin-top: 20px; 
    font-size: 18px; 
}

/* Responsive */
@media (max-width: 1200px) {
    .container {
        max-width: 1140px;
    }
    .tour-two-column-layout {
        grid-template-columns: 1fr 380px !important;
        gap: 32px;
    }
}

@media (max-width: 992px) {
    .container {
        max-width: 100%;
    }
    .tour-two-column-layout { 
        grid-template-columns: 1fr !important;
    }
    .tour-right-column {
        position: static;
        max-width: 100%;
    }
    .gallery-grid { grid-template-columns: 1fr; }
    .gallery-main { height: 400px; }
    .two-col-list { grid-template-columns: 1fr; }
    .info-tabs { flex-wrap: wrap; }
}

@media (max-width: 768px) {
    .title-row { flex-direction: column; }
    .info-tabs { overflow-x: auto; }
    .gallery-thumbs { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
// Info Tabs (No need for main tabs anymore since we removed them)
document.querySelectorAll('.info-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.infoTab;
        
        // Remove active from all buttons
        document.querySelectorAll('.info-tab-btn').forEach(b => b.classList.remove('active'));
        // Add active to clicked button
        this.classList.add('active');
        
        // Hide all tab contents
        document.querySelectorAll('.info-tab-content').forEach(content => content.classList.remove('active'));
        // Show selected tab content
        document.getElementById('info-tab-' + tab).classList.add('active');
    });
});

// Gallery Modal
let currentImageIndex = 0;
const allImages = @json($tour->all_images ?? []);

function openGalleryModal(index) {
    currentImageIndex = index;
    showImage();
    document.getElementById('galleryModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    document.getElementById('galleryModal').classList.remove('active');
    document.body.style.overflow = '';
}

function showImage() {
    if (allImages.length > 0) {
        document.getElementById('modalMainImage').src = allImages[currentImageIndex];
        document.getElementById('currentIndex').textContent = currentImageIndex + 1;
    }
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + allImages.length) % allImages.length;
    showImage();
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % allImages.length;
    showImage();
}

document.addEventListener('keydown', function(e) {
    if (document.getElementById('galleryModal').classList.contains('active')) {
        if (e.key === 'Escape') closeGalleryModal();
        else if (e.key === 'ArrowLeft') prevImage();
        else if (e.key === 'ArrowRight') nextImage();
    }
});

// Wishlist Toggle AJAX
const wishlistBtn = document.getElementById('wishlistBtn');
if (wishlistBtn) {
    wishlistBtn.addEventListener('click', function() {
        const tourId = this.dataset.tourId;
        const btn = this;
        
        fetch('{{ route("user.wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tour_id: tourId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') {
                    btn.classList.add('active');
                    btn.querySelector('i').className = 'bi bi-heart-fill';
                } else if (data.action === 'removed') {
                    btn.classList.remove('active');
                    btn.querySelector('i').className = 'bi bi-heart';
                }
                // Update wishlist count in navbar
                if (typeof updateWishlistCount === 'function') {
                    updateWishlistCount();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
}
</script>
@endpush

@endsection

<!-- Gallery Modal -->
@if($tour->gallery && count($tour->gallery) > 0)
<div class="gallery-modal" id="galleryModal" onclick="if(event.target === this) closeGalleryModal()">
    <button class="modal-close" onclick="closeGalleryModal()"><i class="bi bi-x-lg"></i></button>
    <button class="modal-nav prev" onclick="prevImage()"><i class="bi bi-chevron-left"></i></button>
    <button class="modal-nav next" onclick="nextImage()"><i class="bi bi-chevron-right"></i></button>
    <div class="modal-container">
        <img src="" id="modalMainImage">
        <div class="modal-counter"><span id="currentIndex">1</span> / {{ count($tour->gallery) }}</div>
    </div>
</div>
@endif
