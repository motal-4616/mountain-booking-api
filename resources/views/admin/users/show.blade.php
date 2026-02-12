@extends('admin.layouts.app')

@section('title', 'Chi tiết Người dùng')
@section('page-title', 'Thông tin Chi tiết Người dùng')

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Thông tin cơ bản -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>Thông tin Cơ bản
                </h5>
            </div>
            <div class="card-body-modern text-center">
                <div class="avatar-modern-xl mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                @if($user->is_blocked)
                    <span class="badge-modern badge-danger mb-3">
                        <i class="bi bi-lock-fill me-1"></i>Tài khoản bị khóa
                    </span>
                @endif
                
                @if($user->role === 'super_admin')
                    <span class="badge-modern badge-danger">
                        <i class="bi bi-shield-fill-check me-1"></i>Super Admin
                    </span>
                @elseif($user->role === 'booking_manager')
                    <span class="badge-modern badge-warning">
                        <i class="bi bi-calendar-check me-1"></i>Booking Manager
                    </span>
                @elseif($user->role === 'content_manager')
                    <span class="badge-modern badge-info">
                        <i class="bi bi-file-text me-1"></i>Content Manager
                    </span>
                @else
                    <span class="badge-modern badge-primary">
                        <i class="bi bi-person me-1"></i>User
                    </span>
                @endif
                
                <hr class="my-3">
                
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-primary">{{ $user->bookings->count() }}</h5>
                        <small class="text-muted">Đặt vé</small>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <h5 class="fw-bold mb-0 text-success">
                            {{ number_format($user->bookings->flatMap->successfulPayments->sum('amount')) }}đ
                        </h5>
                        <small class="text-muted">Tổng chi tiêu</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi tiết liên hệ -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Thông tin Liên hệ
                </h5>
            </div>
            <div class="card-body-modern">
                <div class="info-row">
                    <i class="bi bi-envelope text-primary"></i>
                    <div>
                        <small class="text-muted">Email</small>
                        <p class="mb-0">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="info-row">
                    <i class="bi bi-telephone text-success"></i>
                    <div>
                        <small class="text-muted">Số điện thoại</small>
                        <p class="mb-0">{{ $user->phone ?? 'Chưa cập nhật' }}</p>
                    </div>
                </div>
                <div class="info-row">
                    <i class="bi bi-calendar-event text-info"></i>
                    <div>
                        <small class="text-muted">Ngày đăng ký</small>
                        <p class="mb-0">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="info-row">
                    <i class="bi bi-clock-history text-warning"></i>
                    <div>
                        <small class="text-muted">Cập nhật lần cuối</small>
                        <p class="mb-0">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Lịch sử đặt vé -->
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-receipt me-2"></i>Lịch sử Đặt vé ({{ $user->bookings->count() }})
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">
                        Tất cả
                    </button>
                    <button type="button" class="btn btn-outline-success" data-filter="completed">
                        Hoàn thành
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-filter="pending">
                        Đang chờ
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-filter="cancelled">
                        Đã hủy
                    </button>
                </div>
            </div>
            <div class="card-body-modern p-0">
                @if($user->bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="100">Mã đặt vé</th>
                                    <th>Tour</th>
                                    <th width="120">Ngày khởi hành</th>
                                    <th width="100" class="text-end">Số người</th>
                                    <th width="130" class="text-end">Tổng tiền</th>
                                    <th width="130">Thanh toán</th>
                                    <th width="100" class="text-center">Trạng thái</th>
                                    <th width="80" class="text-center">Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->bookings->sortByDesc('created_at') as $booking)
                                    @php
                                        $paymentStatus = $booking->successfulPayments()->exists() ? 'completed' : 
                                                        ($booking->payments()->where('status', 'pending')->exists() ? 'pending' : 'failed');
                                    @endphp
                                    <tr data-status="{{ $paymentStatus }}">
                                        <td>
                                            <span class="badge bg-secondary">#{{ $booking->id }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 200px;">{{ $booking->schedule->tour->name }}</div>
                                            <small class="text-muted">{{ Str::limit($booking->schedule->tour->description, 40) }}</small>
                                        </td>
                                        <td class="text-nowrap">
                                            <i class="bi bi-calendar3 me-1 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($booking->schedule->start_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end">
                                            <i class="bi bi-people-fill me-1 text-info"></i>
                                            <strong>{{ $booking->quantity }}</strong>
                                        </td>
                                        <td class="text-end">
                                            @if($booking->hasCoupon())
                                                <div>
                                                    <small class="text-muted text-decoration-line-through">
                                                        {{ number_format($booking->total_price) }}đ
                                                    </small>
                                                </div>
                                                <div class="fw-bold text-success">
                                                    {{ number_format($booking->final_price) }}đ
                                                </div>
                                                <small class="badge bg-success-subtle text-success">
                                                    -{{ number_format($booking->discount_amount) }}đ
                                                </small>
                                            @else
                                                <div class="fw-bold">{{ number_format($booking->final_price) }}đ</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($paymentStatus === 'completed')
                                                <span class="badge-modern badge-success">
                                                    <i class="bi bi-check-circle me-1"></i>Đã thanh toán
                                                </span>
                                            @elseif($paymentStatus === 'pending')
                                                <span class="badge-modern badge-warning">
                                                    <i class="bi bi-clock me-1"></i>Chờ thanh toán
                                                </span>
                                            @else
                                                <span class="badge-modern badge-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Thất bại
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($booking->status === 'confirmed')
                                                <span class="badge bg-success">Đã xác nhận</span>
                                            @elseif($booking->status === 'pending')
                                                <span class="badge bg-warning">Chờ xác nhận</span>
                                            @else
                                                <span class="badge bg-secondary">Đã hủy</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.bookings.show', $booking) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-pill" 
                                               title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
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
                        <h5>Chưa có lịch sử đặt vé</h5>
                        <p class="text-muted">Người dùng này chưa thực hiện đặt vé nào.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="d-flex gap-2 mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-modern btn-secondary-modern">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-modern btn-primary-modern">
        <i class="bi bi-pencil me-2"></i>Chỉnh sửa
    </a>
    @if($user->id !== auth()->id())
        <form action="{{ route('admin.users.toggleBlock', $user) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-modern {{ $user->is_blocked ? 'btn-success-modern' : 'btn-warning-modern' }}">
                <i class="bi {{ $user->is_blocked ? 'bi-unlock' : 'bi-lock' }} me-2"></i>
                {{ $user->is_blocked ? 'Mở khóa' : 'Khóa người dùng' }}
            </button>
        </form>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" id="deleteUserForm">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-modern btn-danger-modern" id="btnDeleteUser">
                <i class="bi bi-trash me-2"></i>Xóa người dùng
            </button>
        </form>
    @endif
</div>

<style>
.avatar-modern-xl {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    border-radius: 50%;
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.info-row {
    display: flex;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row i {
    font-size: 1.2rem;
    width: 30px;
    text-align: center;
}

.info-row p {
    word-break: break-word;
}

/* Cải thiện bảng lịch sử đặt vé */
.table-responsive {
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}

.table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 13px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 12px;
}

.table tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateX(2px);
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.badge {
    padding: 6px 12px;
    font-weight: 500;
    font-size: 12px;
    border-radius: 6px;
}

.badge-modern {
    padding: 6px 12px;
    font-weight: 500;
    font-size: 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-group-sm .btn {
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-group-sm .btn.active {
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
// Filter bookings by status
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active button
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter rows
        document.querySelectorAll('tbody tr[data-status]').forEach(row => {
            if (filter === 'all' || row.dataset.status === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Delete user confirmation
document.getElementById('btnDeleteUser')?.addEventListener('click', function() {
    const form = document.getElementById('deleteUserForm');
    confirmDelete('Bạn có chắc muốn xóa người dùng này?<br><small class="text-danger">Tất cả dữ liệu liên quan sẽ bị xóa vĩnh viễn.</small>', function() {
        form.submit();
    });
});
</script>
@endsection
