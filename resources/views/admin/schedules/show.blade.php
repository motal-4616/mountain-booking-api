@extends('admin.layouts.app')

@section('title', 'Chi tiết Lịch trình #' . $schedule->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-calendar3"></i> Chi tiết Lịch trình #{{ $schedule->id }}
        </h2>
        <div>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Sửa
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Thông tin lịch trình -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin lịch trình</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Tour:</th>
                                <td>
                                    <a href="{{ route('admin.tours.edit', $schedule->tour) }}">
                                        {{ $schedule->tour->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Ngày khởi hành:</th>
                                <td>{{ $schedule->departure_date->format('d/m/Y') }}</td>
                            </tr>
                            @if($schedule->end_date)
                            <tr>
                                <th>Ngày kết thúc:</th>
                                <td>{{ $schedule->end_date->format('d/m/Y') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Số người tối đa:</th>
                                <td>{{ $schedule->max_people }} người</td>
                            </tr>
                            <tr>
                                <th>Số chỗ còn lại:</th>
                                <td>
                                    <span class="badge {{ $schedule->available_slots > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $schedule->available_slots }} chỗ
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Số người đã đặt:</th>
                                <td>{{ $schedule->max_people - $schedule->available_slots }} người</td>
                            </tr>
                            <tr>
                                <th>Giá:</th>
                                <td class="fw-bold text-success">{{ number_format($schedule->price, 0, ',', '.') }}đ</td>
                            </tr>
                            <tr>
                                <th>Trạng thái:</th>
                                <td>
                                    @if($schedule->is_active)
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Tạm dừng</span>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($schedule->min_people))
                            <tr>
                                <th>Số người tối thiểu:</th>
                                <td>{{ $schedule->min_people }} người</td>
                            </tr>
                            @endif
                            @if(isset($schedule->registration_deadline))
                            <tr>
                                <th>Deadline đăng ký:</th>
                                <td>{{ $schedule->registration_deadline->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Danh sách bookings -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách đặt vé ({{ $schedule->bookings->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($schedule->bookings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Số người</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule->bookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>
                                            <div>{{ $booking->user->name }}</div>
                                            <small class="text-muted">{{ $booking->contact_phone }}</small>
                                        </td>
                                        <td>{{ $booking->quantity }}</td>
                                        <td class="fw-bold">{{ number_format($booking->final_price, 0, ',', '.') }}đ</td>
                                        <td>
                                            <span class="badge bg-{{ $booking->status_badge }}">
                                                {{ $booking->status_text }}
                                            </span>
                                        </td>
                                        <td>{{ $booking->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">Chưa có booking nào</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Thống kê -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalBookings = $schedule->bookings->count();
                        $confirmedBookings = $schedule->bookings->where('status', 'confirmed')->count();
                        $pendingBookings = $schedule->bookings->where('status', 'pending')->count();
                        $cancelledBookings = $schedule->bookings->where('status', 'cancelled')->count();
                        $totalRevenue = $schedule->bookings->whereIn('status', ['confirmed', 'completed'])->sum('final_price');
                    @endphp
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Tổng booking</small>
                        <h4 class="mb-0">{{ $totalBookings }}</h4>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Đã xác nhận</small>
                        <h4 class="mb-0 text-success">{{ $confirmedBookings }}</h4>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Đang chờ</small>
                        <h4 class="mb-0 text-warning">{{ $pendingBookings }}</h4>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Đã hủy</small>
                        <h4 class="mb-0 text-danger">{{ $cancelledBookings }}</h4>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <small class="text-muted d-block">Doanh thu ước tính</small>
                        <h4 class="mb-0 text-primary">{{ number_format($totalRevenue, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thao tác</h5>
                </div>
                <div class="card-body">
                    @if($schedule->is_active)
                        <form action="{{ route('admin.schedules.bulk-action') }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="schedule_ids[]" value="{{ $schedule->id }}">
                            <input type="hidden" name="action" value="deactivate">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-pause-circle"></i> Tạm dừng
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.schedules.bulk-action') }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="schedule_ids[]" value="{{ $schedule->id }}">
                            <input type="hidden" name="action" value="activate">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-play-circle"></i> Kích hoạt
                            </button>
                        </form>
                    @endif
                    
                    @if($schedule->bookings->whereIn('status', ['pending', 'confirmed'])->count() == 0)
                        <form action="{{ route('admin.schedules.bulk-action') }}" method="POST" id="deleteScheduleForm">
                            @csrf
                            <input type="hidden" name="schedule_ids[]" value="{{ $schedule->id }}">
                            <input type="hidden" name="action" value="delete">
                            <button type="button" class="btn btn-danger w-100" id="btnDeleteSchedule">
                                <i class="bi bi-trash"></i> Xóa lịch trình
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary w-100" disabled>
                            <i class="bi bi-trash"></i> Không thể xóa (có booking active)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnDeleteSchedule')?.addEventListener('click', function() {
    const form = document.getElementById('deleteScheduleForm');
    confirmDelete('Bạn có chắc muốn xóa lịch trình này?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>', function() {
        form.submit();
    });
});
</script>
@endpush
@endsection
