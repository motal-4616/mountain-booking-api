@extends('admin.layouts.app')

@section('title', 'Booking Manager Dashboard')
@section('page-title', 'Booking Manager Dashboard')

@section('content')
<!-- Header với greeting -->
<div class="mb-4">
    <h4 class="mb-1">Chào mừng, {{ auth()->user()->name }}! 👋</h4>
    <p class="text-muted mb-0">Quản lý đặt vé và thanh toán</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Total Bookings -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['total_bookings']) }}</div>
            <div class="stat-card-label">Tổng Bookings</div>
            <small class="text-muted">{{ $stats['today_bookings'] }} hôm nay</small>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['pending_bookings'] }}</div>
            <div class="stat-card-label">Chờ xác nhận</div>
            @if($stats['today_pending'] > 0)
                <small class="text-warning"><i class="bi bi-exclamation-circle"></i> {{ $stats['today_pending'] }} hôm nay</small>
            @else
                <small class="text-muted">Không có mới hôm nay</small>
            @endif
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['revenue_this_month'] / 1000000, 1) }}M</div>
            <div class="stat-card-label">Doanh thu tháng này</div>
            <small class="text-muted">{{ number_format($stats['revenue_this_month'], 0, ',', '.') }}đ</small>
        </div>
    </div>

    <!-- Upcoming Schedules -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['upcoming_schedules'] }}</div>
            <div class="stat-card-label">Lịch trong 7 ngày</div>
            <small class="text-muted">Cần theo dõi</small>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-success">{{ $stats['confirmed_bookings'] }}</h3>
                <small class="text-muted">Đã xác nhận</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-info">{{ $stats['completed_bookings'] }}</h3>
                <small class="text-muted">Đã hoàn thành</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-danger">{{ $stats['cancelled_bookings'] }}</h3>
                <small class="text-muted">Đã hủy</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-warning">{{ $stats['pending_payments'] }}</h3>
                <small class="text-muted">Thanh toán chờ duyệt</small>
            </div>
        </div>
    </div>
</div>

<!-- Booking Chart -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-graph-up"></i> Booking 7 ngày qua</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="bookingChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-pie-chart"></i> Trạng thái</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Pending Bookings & Upcoming Schedules -->
<div class="row g-4 mb-4">
    <!-- Pending Bookings -->
    <div class="col-lg-7">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-clock-history text-warning"></i> Bookings chờ xác nhận</h5>
                <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($pendingBookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingBookings as $booking)
                                    <tr>
                                        <td><a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-primary fw-bold">#{{ $booking->id }}</a></td>
                                        <td>
                                            <div class="fw-bold">{{ $booking->contact_name }}</div>
                                            <small class="text-muted">{{ $booking->contact_phone }}</small>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($booking->schedule->tour->name, 25) }}</small><br>
                                            <small class="text-muted">{{ $booking->schedule->departure_date->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $booking->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-primary" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        <p class="mb-0">Không có booking chờ xác nhận</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Schedules -->
    <div class="col-lg-5">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-calendar-event text-info"></i> Lịch sắp khởi hành</h5>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($upcomingSchedules->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcomingSchedules as $schedule)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($schedule->tour->name, 30) }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> {{ $schedule->departure_date->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-info">
                                        {{ $schedule->bookings->count() }}/{{ $schedule->max_people }}
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: {{ ($schedule->bookings->count() / $schedule->max_people) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Không có lịch trình trong 7 ngày tới</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="row g-4">
    <div class="col-12">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-receipt"></i> Bookings mới nhất</h5>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($recentBookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Số người</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                    <tr>
                                        <td><a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-primary fw-bold">#{{ $booking->id }}</a></td>
                                        <td>
                                            <div class="fw-bold">{{ $booking->contact_name }}</div>
                                            <small class="text-muted">{{ $booking->contact_email }}</small>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($booking->schedule->tour->name, 30) }}</small>
                                        </td>
                                        <td>{{ $booking->number_of_people }} người</td>
                                        <td class="fw-bold text-success">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</td>
                                        <td>{!! $booking->status_badge !!}</td>
                                        <td><small>{{ $booking->created_at->format('d/m/Y H:i') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Chưa có booking nào</p>
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
// Booking Chart (7 days)
const bookingCtx = document.getElementById('bookingChart').getContext('2d');
new Chart(bookingCtx, {
    type: 'bar',
    data: {
        labels: @json($bookingChart['labels']),
        datasets: [{
            label: 'Số booking',
            data: @json($bookingChart['data']),
            backgroundColor: '#6366f1',
            borderRadius: 6
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

// Status Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
        datasets: [{
            data: [
                {{ $bookingsByStatus['pending'] ?? 0 }},
                {{ $bookingsByStatus['confirmed'] ?? 0 }},
                {{ $bookingsByStatus['completed'] ?? 0 }},
                {{ ($bookingsByStatus['cancelled'] ?? 0) + ($bookingsByStatus['refunded'] ?? 0) }}
            ],
            backgroundColor: ['#f59e0b', '#10b981', '#06b6d4', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});
</script>
@endpush
