@extends('admin.layouts.app')

@section('title', 'Chi tiết mã giảm giá')
@section('page-title', 'Chi Tiết Mã Giảm Giá')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Thông tin chung -->
        <div class="card-modern mb-4">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-ticket-perforated me-2"></i>Mã: <code class="fs-5">{{ $coupon->code }}</code></h5>
                <span class="badge {{ $coupon->status_badge_class }} fs-6">{{ $coupon->status_text }}</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Tên:</th>
                                <td>{{ $coupon->name }}</td>
                            </tr>
                            <tr>
                                <th>Mô tả:</th>
                                <td>{{ $coupon->description ?: 'Không có' }}</td>
                            </tr>
                            <tr>
                                <th>Loại giảm:</th>
                                <td>
                                    @if($coupon->type == 'percent')
                                        <span class="badge bg-info">Phần trăm</span>
                                    @else
                                        <span class="badge bg-success">Số tiền cố định</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Giá trị:</th>
                                <td><strong class="text-primary fs-5">{{ $coupon->value_display }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Đơn tối thiểu:</th>
                                <td>{{ $coupon->min_order_amount > 0 ? number_format($coupon->min_order_amount, 0, ',', '.') . 'đ' : 'Không giới hạn' }}</td>
                            </tr>
                            @if($coupon->type == 'percent')
                            <tr>
                                <th>Giảm tối đa:</th>
                                <td>{{ $coupon->max_discount ? number_format($coupon->max_discount, 0, ',', '.') . 'đ' : 'Không giới hạn' }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Thời gian:</th>
                                <td>
                                    {{ $coupon->start_date->format('d/m/Y') }} ~ {{ $coupon->end_date->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Người tạo:</th>
                                <td>{{ $coupon->creator->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                    </a>
                    <form action="{{ route('admin.coupons.toggleStatus', $coupon) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-{{ $coupon->is_active ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $coupon->is_active ? 'pause' : 'play' }} me-1"></i>
                            {{ $coupon->is_active ? 'Tắt mã' : 'Bật mã' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>

        <!-- Danh sách bookings đã sử dụng -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-list-check me-2"></i>Đơn đặt vé đã sử dụng mã này</h5>
            </div>
            <div class="card-body-modern p-0">
                @if($coupon->bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Giảm giá</th>
                                    <th>Thanh toán</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupon->bookings as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="fw-bold">
                                                #{{ $booking->id }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $booking->user->name ?? $booking->contact_name }}
                                            <br><small class="text-muted">{{ $booking->contact_phone }}</small>
                                        </td>
                                        <td>{{ $booking->schedule->tour->name ?? 'N/A' }}</td>
                                        <td class="text-success fw-bold">
                                            -{{ number_format($booking->discount_amount, 0, ',', '.') }}đ
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $booking->payment_status_badge }}">
                                                {{ $booking->payment_status_text }}
                                            </span>
                                        </td>
                                        <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-3">Chưa có đơn đặt vé nào sử dụng mã này.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Thống kê -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart me-2"></i>Thống kê</h5>
            </div>
            <div class="card-body-modern">
                <!-- Lượt sử dụng -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Lượt sử dụng:</span>
                        <strong>{{ $coupon->used_count }}@if($coupon->usage_limit) / {{ $coupon->usage_limit }}@endif</strong>
                    </div>
                    @if($coupon->usage_limit)
                        @php $usagePercent = min(100, ($coupon->used_count / $coupon->usage_limit) * 100); @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-{{ $usagePercent >= 90 ? 'danger' : ($usagePercent >= 70 ? 'warning' : 'success') }}" 
                                 style="width: {{ $usagePercent }}%"></div>
                        </div>
                        <small class="text-muted">Còn lại: {{ $coupon->remaining_usage }} lượt</small>
                    @else
                        <small class="text-muted">Không giới hạn lượt sử dụng</small>
                    @endif
                </div>

                <hr>

                <!-- Tổng giảm giá -->
                <div class="text-center mb-3">
                    <small class="text-muted d-block">Tổng tiền đã giảm</small>
                    <span class="fs-3 fw-bold text-danger">
                        {{ number_format($stats['total_discount'], 0, ',', '.') }}đ
                    </span>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <small class="text-muted d-block">Số đơn hàng</small>
                        <span class="fs-5 fw-bold">{{ $stats['total_bookings'] }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Doanh thu</small>
                        <span class="fs-5 fw-bold text-success">{{ number_format($stats['total_revenue'], 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin thời gian -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-clock me-2"></i>Thời gian</h5>
            </div>
            <div class="card-body-modern">
                <div class="d-flex justify-content-between mb-2">
                    <span>Ngày tạo:</span>
                    <span>{{ $coupon->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Cập nhật lần cuối:</span>
                    <span>{{ $coupon->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Bắt đầu:</span>
                    <span>{{ $coupon->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Kết thúc:</span>
                    <span>{{ $coupon->end_date->format('d/m/Y') }}</span>
                </div>
                
                @php
                    $today = now();
                    $daysLeft = $today->diffInDays($coupon->end_date, false);
                @endphp
                
                @if($coupon->start_date > $today)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-calendar-event me-1"></i>
                        Còn {{ $today->diffInDays($coupon->start_date) }} ngày nữa bắt đầu
                    </div>
                @elseif($daysLeft > 0)
                    <div class="alert alert-{{ $daysLeft <= 7 ? 'warning' : 'success' }} mt-3 mb-0">
                        <i class="bi bi-hourglass-split me-1"></i>
                        Còn {{ $daysLeft }} ngày hiệu lực
                    </div>
                @else
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-calendar-x me-1"></i>
                        Đã hết hạn
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
