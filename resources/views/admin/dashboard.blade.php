@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <i class="bi bi-compass"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['total_tours'] }}</div>
            <div class="stat-card-label">Tour leo núi</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['total_users'] }}</div>
            <div class="stat-card-label">Người dùng</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['total_bookings'] }}</div>
            <div class="stat-card-label">Tổng đơn đặt</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['total_revenue'] / 1000000, 1) }}M</div>
            <div class="stat-card-label">Đã thu (VNĐ)</div>
        </div>
    </div>
</div>

<!-- Thống kê thanh toán -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-label mb-2">Tổng đơn đặt vé</div>
                    <div class="stat-card-value" style="font-size: 28px;">{{ $stats['total_bookings'] }}</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 50px; height: 50px; font-size: 20px;">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-label mb-2">Đơn thanh toán 1 phần</div>
                    <div class="stat-card-value" style="font-size: 28px; color: var(--accent-orange);">{{ $stats['partial_payment_count'] }}</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); width: 50px; height: 50px; font-size: 20px;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-card-label mb-2">Tiền chờ thu</div>
                    <div class="stat-card-value" style="font-size: 24px; color: #ef4444;">{{ number_format($stats['pending_payment'] / 1000000, 1) }}M đ</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); width: 50px; height: 50px; font-size: 20px;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Bookings -->
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-receipt"></i> Đơn đặt vé mới nhất</h5>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
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
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                    <tr>
                                        <td><span class="fw-bold text-muted">#{{ $booking->id }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle" style="width:32px;height:32px;background:linear-gradient(135deg, #10b981 0%, #059669 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:12px;">
                                                    {{ strtoupper(substr($booking->user->name, 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $booking->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($booking->schedule->tour->name, 20) }}</td>
                                        <td><span class="fw-bold text-primary">{{ $booking->formatted_price }}</span></td>
                                        <td>
                                            <span class="badge-modern badge-{{ $booking->status_badge }}">
                                                {{ $booking->status_text }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h3>Chưa có đơn đặt vé nào</h3>
                        <p>Đơn đặt vé sẽ xuất hiện ở đây khi khách hàng đặt tour</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Schedules -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-calendar3"></i> Lịch trình sắp tới</h5>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-modern btn-sm-modern btn-primary-modern">
                    Xem <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body-modern p-0">
                @if($upcomingSchedules->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcomingSchedules as $schedule)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($schedule->tour->name, 15) }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $schedule->formatted_date }}
                                        </small>
                                    </div>
                                    <span class="badge-modern {{ $schedule->available_slots > 5 ? 'badge-success' : 'badge-warning' }}">
                                        {{ $schedule->available_slots }}/{{ $schedule->max_people }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 40px 20px;">
                        <div class="empty-state-icon" style="width: 60px; height: 60px;">
                            <i class="bi bi-calendar-x" style="font-size: 24px;"></i>
                        </div>
                        <h3 style="font-size: 16px;">Chưa có lịch trình</h3>
                        <p style="font-size: 14px;">Thêm lịch trình để bắt đầu</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
