@extends('admin.layouts.app')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'Super Admin Dashboard')

@section('content')
<!-- Header với greeting -->
<div class="mb-4">
    <h4 class="mb-1">Chào mừng, {{ auth()->user()->name }}! 👋</h4>
    <p class="text-muted mb-0">Tổng quan toàn bộ hệ thống Mountain Booking</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Users -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-card-label">Người dùng</div>
            <small class="text-success"><i class="bi bi-arrow-up"></i> +{{ $stats['new_users_this_month'] }} tháng này</small>
        </div>
    </div>

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

    <!-- Bookings -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['total_bookings']) }}</div>
            <div class="stat-card-label">Tổng Bookings</div>
            @if($stats['pending_bookings'] > 0)
                <small class="text-warning"><i class="bi bi-exclamation-circle"></i> {{ $stats['pending_bookings'] }} chờ xử lý</small>
            @else
                <small class="text-success"><i class="bi bi-check-circle"></i> Không có pending</small>
            @endif
        </div>
    </div>

    <!-- Revenue -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['total_revenue'], 0, ',', '.') }}đ</div>
            <div class="stat-card-label">Tổng doanh thu</div>
            <small class="text-muted">{{ number_format($stats['revenue_this_month'], 0, ',', '.') }}đ tháng này</small>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-4 mb-4">
    <!-- Admins -->
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-primary">{{ $stats['total_admins'] }}</h3>
                <small class="text-muted">Quản trị viên</small>
            </div>
        </div>
    </div>
    
    <!-- Schedules -->
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-info">{{ $stats['upcoming_schedules'] }}</h3>
                <small class="text-muted">Lịch trình sắp tới</small>
            </div>
        </div>
    </div>
    
    <!-- Reviews -->
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-warning">{{ $stats['pending_reviews'] }}</h3>
                <small class="text-muted">Reviews chờ duyệt</small>
            </div>
        </div>
    </div>
    
    <!-- Coupons -->
    <div class="col-md-3">
        <div class="card-modern text-center">
            <div class="card-body-modern py-3">
                <h3 class="mb-1 text-success">{{ $stats['active_coupons'] }}</h3>
                <small class="text-muted">Mã giảm giá hoạt động</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-graph-up"></i> Doanh thu 12 tháng</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Status Pie Chart -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-pie-chart"></i> Trạng thái Booking</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Content Row -->
<div class="row g-4 mb-4">
    <!-- Top Tours by Revenue -->
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-trophy"></i> Top Tours (Doanh thu)</h5>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($topTours->count() > 0)
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th>Địa điểm</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topTours as $tour)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $tour->name }}</div>
                                            <small class="text-muted">{{ $tour->duration_days }} ngày {{ $tour->duration_days - 1 }} đêm</small>
                                        </td>
                                        <td>{{ $tour->location }}</td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">
                                                {{ number_format($tour->total_revenue, 0, ',', '.') }}đ
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Chưa có dữ liệu
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="col-lg-6">
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
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                    <tr>
                                        <td><a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-primary">#{{ $booking->id }}</a></td>
                                        <td>
                                            <div class="fw-bold">{{ $booking->contact_name }}</div>
                                            <small class="text-muted">{{ $booking->contact_email }}</small>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($booking->schedule->tour->name, 30) }}</small>
                                        </td>
                                        <td>{!! $booking->status_badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Chưa có booking nào
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
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($revenueChart['labels']),
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: @json($revenueChart['data']),
            borderColor: '#06b6d4',
            backgroundColor: 'rgba(6, 182, 212, 0.1)',
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
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            }
        }
    }
});

// Booking Status Pie Chart
const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
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
