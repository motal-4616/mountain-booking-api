@extends('admin.layouts.app')

@section('title', 'Content Manager Dashboard')
@section('page-title', 'Content Manager Dashboard')

@section('content')
<!-- Header với greeting -->
<div class="mb-4">
    <h4 class="mb-1">Chào mừng, {{ auth()->user()->name }}! 👋</h4>
    <p class="text-muted mb-0">Quản lý nội dung Tour, Đánh giá và Liên hệ</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Tours -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <i class="bi bi-compass"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['total_tours'] }}</div>
            <div class="stat-card-label">Tổng Tours</div>
            <small class="text-muted">{{ $stats['active_tours'] }} đang hoạt động</small>
        </div>
    </div>

    <!-- Schedules -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['upcoming_schedules'] }}</div>
            <div class="stat-card-label">Lịch trình sắp tới</div>
            <small class="text-muted">{{ $stats['total_schedules'] }} tổng cộng</small>
        </div>
    </div>

    <!-- Reviews -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-star"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['pending_reviews'] }}</div>
            <div class="stat-card-label">Reviews chờ duyệt</div>
            @if($stats['avg_rating'])
                <small class="text-muted"><i class="bi bi-star-fill text-warning"></i> {{ $stats['avg_rating'] }} điểm TB</small>
            @else
                <small class="text-muted">Chưa có đánh giá</small>
            @endif
        </div>
    </div>

    <!-- Contacts -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-envelope"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['unread_contacts'] }}</div>
            <div class="stat-card-label">Liên hệ chưa đọc</div>
            <small class="text-muted">{{ $stats['contacts_this_week'] }} tuần này</small>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-success">{{ $stats['approved_reviews'] }}</h3>
                <small class="text-muted">Reviews đã duyệt</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-info">{{ $stats['active_schedules'] }}</h3>
                <small class="text-muted">Lịch trình hoạt động</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-warning">{{ $stats['tours_this_month'] }}</h3>
                <small class="text-muted">Tours tạo tháng này</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-danger">{{ $toursNeedSchedule->count() }}</h3>
                <small class="text-muted">Tours cần thêm lịch</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Review Chart -->
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-graph-up"></i> Đánh giá 6 tháng</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="reviewChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart"></i> Phân bố Rating</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Content Management Row -->
<div class="row g-4 mb-4">
    <!-- Pending Reviews -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-star text-warning"></i> Reviews chờ duyệt</h5>
                <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($pendingReviews->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pendingReviews->take(5) as $review)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0">{{ $review->user->name }}</h6>
                                        <small class="text-muted">{{ $review->tour->name }}</small>
                                    </div>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star-fill {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-2 small">{{ Str::limit($review->comment, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-sm btn-primary">
                                        Xem & Duyệt
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        <p class="mb-0">Không có review chờ duyệt</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Unread Contacts -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-envelope text-info"></i> Liên hệ chưa đọc</h5>
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($unreadContacts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($unreadContacts as $contact)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0">{{ $contact->name }}</h6>
                                        <small class="text-muted">{{ $contact->email }}</small>
                                    </div>
                                    <span class="badge bg-primary">Mới</span>
                                </div>
                                <p class="mb-2 small"><strong>{{ $contact->subject }}</strong></p>
                                <p class="mb-2 small text-muted">{{ Str::limit($contact->message, 80) }}</p>
                                <small class="text-muted">{{ $contact->created_at->diffForHumans() }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Không có liên hệ chưa đọc</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Tours & Schedules Row -->
<div class="row g-4 mb-4">
    <!-- Tours Need Schedule -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-exclamation-triangle text-warning"></i> Tours cần thêm lịch</h5>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($toursNeedSchedule->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($toursNeedSchedule as $tour)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $tour->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $tour->location }}
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.schedules.create', ['tour_id' => $tour->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-plus-circle"></i> Thêm lịch
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        <p class="mb-0">Tất cả tours đều có lịch trình</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Popular Tours -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-trophy text-success"></i> Tours phổ biến</h5>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($popularTours->count() > 0)
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th class="text-center">Bookings</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularTours as $tour)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ Str::limit($tour->name, 35) }}</div>
                                            <small class="text-muted">{{ $tour->location }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $tour->bookings_count ?? 0 }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.tours.edit', $tour->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Chưa có dữ liệu</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Tours & Almost Full Schedules -->
<div class="row g-4">
    <!-- Recent Tours -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-clock-history"></i> Tours mới nhất</h5>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($recentTours->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentTours as $tour)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $tour->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $tour->location }} | 
                                            <i class="bi bi-calendar3"></i> {{ $tour->duration }} ngày
                                        </small>
                                    </div>
                                    <span class="badge {{ $tour->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $tour->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Chưa có tour nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Almost Full Schedules -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-exclamation-circle text-danger"></i> Lịch sắp đầy</h5>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($almostFullSchedules->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($almostFullSchedules as $schedule)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($schedule->tour->name, 30) }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> {{ $schedule->departure_date->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-danger">
                                        {{ $schedule->available_slots }}/{{ $schedule->max_people }} còn
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $filled = (($schedule->max_people - $schedule->available_slots) / $schedule->max_people) * 100;
                                    @endphp
                                    <div class="progress-bar bg-danger" style="width: {{ $filled }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Không có lịch trình nào sắp đầy</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Review Chart
const reviewCtx = document.getElementById('reviewChart').getContext('2d');
new Chart(reviewCtx, {
    type: 'line',
    data: {
        labels: @json($reviewChart['labels']),
        datasets: [{
            label: 'Số reviews',
            data: @json($reviewChart['data']),
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Rating Distribution Chart
const ratingCtx = document.getElementById('ratingChart').getContext('2d');
new Chart(ratingCtx, {
    type: 'bar',
    data: {
        labels: ['5⭐', '4⭐', '3⭐', '2⭐', '1⭐'],
        datasets: [{
            label: 'Số lượng',
            data: [
                {{ $ratingStats[5] ?? 0 }},
                {{ $ratingStats[4] ?? 0 }},
                {{ $ratingStats[3] ?? 0 }},
                {{ $ratingStats[2] ?? 0 }},
                {{ $ratingStats[1] ?? 0 }}
            ],
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#991b1b'],
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
