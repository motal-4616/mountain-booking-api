@extends('admin.layouts.app')

@section('title', 'Quản lý Lịch trình')
@section('page-title', 'Quản lý Lịch Trình')

@section('content')
<!-- Header với filter -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-compass me-1"></i>Tour
                </label>
                <select name="tour_id" class="form-select form-control-modern">
                    <option value="">Tất cả tour</option>
                    @foreach($tours as $tour)
                        <option value="{{ $tour->id }}" {{ request('tour_id') == $tour->id ? 'selected' : '' }}>
                            {{ $tour->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i>Từ ngày
                </label>
                <input type="date" name="from_date" class="form-control form-control-modern" 
                       value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i>Đến ngày
                </label>
                <input type="date" name="to_date" class="form-control form-control-modern" 
                       value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-toggle-on me-1"></i>Trạng thái
                </label>
                <select name="status" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                    <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Đã qua</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-funnel"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                @if(request()->hasAny(['tour_id', 'from_date', 'to_date', 'status']))
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-modern btn-secondary-modern w-100">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @else
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-modern btn-primary-modern w-100">
                        <i class="bi bi-plus-circle me-1"></i>Thêm
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkActionsBar" class="card-modern mb-3" style="display: none;">
    <div class="card-body-modern">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span id="selectedCount" class="fw-semibold">0</span> lịch trình đã chọn
            </div>
            <div class="col-md-6 text-end">
                <form id="bulkActionForm" method="POST" action="{{ route('admin.schedules.bulk-action') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="ids" id="bulkIds">
                    <select name="action" id="bulkAction" class="form-select form-select-sm d-inline-block w-auto me-2" required>
                        <option value="">-- Chọn hành động --</option>
                        <option value="activate">Kích hoạt</option>
                        <option value="deactivate">Tạm dừng</option>
                        <option value="delete">Xóa</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Thực hiện</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">Hủy</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách lịch trình -->
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-calendar3"></i> Danh sách Lịch trình</h5>
        <span class="badge-modern badge-primary">{{ $schedules->total() }} lịch trình</span>
    </div>
    <div class="card-body-modern p-0">
        @if($schedules->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Chọn tất cả">
                            </th>
                            <th>ID</th>
                            <th>Tour</th>
                            <th>Ngày khởi hành</th>
                            <th>Ngày kết thúc</th>
                            <th>Thời gian</th>
                            <th>Giá tour</th>
                            <th>Số người</th>
                            <th>Còn trống</th>
                            <th>Đơn đặt</th>
                            <th>Trạng thái</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input schedule-checkbox" value="{{ $schedule->id }}">
                                </td>
                                <td><span class="fw-bold text-muted">#{{ $schedule->id }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($schedule->tour->name, 25) }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $schedule->tour->location ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 rounded-3 p-2 text-center" style="min-width: 50px;">
                                            <div class="fw-bold text-primary" style="font-size: 18px;">
                                                {{ \Carbon\Carbon::parse($schedule->departure_date)->format('d') }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($schedule->departure_date)->format('M') }}
                                            </div>
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Carbon\Carbon::parse($schedule->departure_date)->format('Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($schedule->end_date)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-success bg-opacity-10 rounded-3 p-2 text-center" style="min-width: 50px;">
                                                <div class="fw-bold text-success" style="font-size: 18px;">
                                                    {{ \Carbon\Carbon::parse($schedule->end_date)->format('d') }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ \Carbon\Carbon::parse($schedule->end_date)->format('M') }}
                                                </div>
                                            </div>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($schedule->end_date)->format('Y') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $days = $schedule->end_date 
                                            ? \Carbon\Carbon::parse($schedule->departure_date)->diffInDays(\Carbon\Carbon::parse($schedule->end_date)) + 1
                                            : 1;
                                    @endphp
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-clock me-1"></i>{{ $days }} ngày
                                    </span>
                                    @if($schedule->end_date && $schedule->end_date < now())
                                        <br><span class="badge-modern badge-secondary mt-1">Đã kết thúc</span>
                                    @elseif($schedule->departure_date < now() && (!$schedule->end_date || $schedule->end_date >= now()))
                                        <br><span class="badge-modern badge-primary mt-1">Đang diễn ra</span>
                                    @elseif($schedule->departure_date < now()->addDays(7))
                                        <br><span class="badge-modern badge-warning mt-1">Sắp tới</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($schedule->price, 0, ',', '.') }}₫</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $schedule->max_people }}</span>
                                    <span class="text-muted">người</span>
                                </td>
                                <td>
                                    @php
                                        $percent = $schedule->max_people > 0 
                                            ? ($schedule->available_slots / $schedule->max_people) * 100 
                                            : 0;
                                    @endphp
                                    @if($schedule->available_slots == 0)
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-x-circle me-1"></i>Hết chỗ
                                        </span>
                                    @elseif($percent <= 30)
                                        <span class="badge-modern badge-warning">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $schedule->available_slots }} chỗ
                                        </span>
                                    @else
                                        <span class="badge-modern badge-success">
                                            <i class="bi bi-check-circle me-1"></i>{{ $schedule->available_slots }} chỗ
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-modern badge-info">
                                        <i class="bi bi-receipt me-1"></i>{{ $schedule->bookings_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($schedule->is_active)
                                        <span class="badge-modern badge-success">
                                            <i class="bi bi-check-circle"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-pause-circle"></i> Tạm dừng
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.schedules.show', $schedule) }}" 
                                           class="btn btn-sm btn-outline-info rounded-pill" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete" 
                                                    title="Xóa"
                                                    data-name="{{ $schedule->tour->name }} - {{ $schedule->formatted_date }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
                <div class="card-body-modern border-top">
                    {{ $schedules->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3>Chưa có lịch trình nào</h3>
                <p>Thêm lịch trình cho các tour để khách hàng có thể đặt</p>
                <a href="{{ route('admin.schedules.create') }}" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-plus-circle me-2"></i>Thêm lịch trình đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// Select All functionality
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.schedule-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

// Update bulk actions bar visibility and count
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.schedule-checkbox:checked');
    const count = checkedBoxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkBar.style.display = 'block';
        selectedCount.textContent = count;
        
        // Update hidden input with selected IDs
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('bulkIds').value = ids.join(',');
    } else {
        bulkBar.style.display = 'none';
        document.getElementById('selectAll').checked = false;
    }
}

// Clear selection
function clearSelection() {
    document.querySelectorAll('.schedule-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Delete confirmation với Popup
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const name = this.dataset.name || 'lịch trình này';
        
        confirmDelete(`Bạn có chắc muốn xóa <strong>${name}</strong>?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>`, function() {
            form.submit();
        });
    });
});

// Confirm bulk action với Popup
document.getElementById('bulkActionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const action = document.getElementById('bulkAction').value;
    
    if (!action) {
        Toast.warning('Vui lòng chọn hành động!');
        return;
    }
    
    const count = document.querySelectorAll('.schedule-checkbox:checked').length;
    let message = '';
    let isDanger = false;
    
    if (action === 'activate') {
        message = `Bạn có chắc muốn <strong>kích hoạt ${count} lịch trình</strong>?`;
    } else if (action === 'deactivate') {
        message = `Bạn có chắc muốn <strong>tạm dừng ${count} lịch trình</strong>?`;
    } else if (action === 'delete') {
        message = `Bạn có chắc muốn <strong>XÓA ${count} lịch trình</strong>?<br><small class="text-danger">Hành động này không thể hoàn tác!</small>`;
        isDanger = true;
    }
    
    confirmAction(message, function() {
        form.submit();
    }, {
        title: isDanger ? 'Xác nhận xóa' : 'Xác nhận thao tác',
        confirmText: isDanger ? 'Xóa' : 'Xác nhận',
        isDanger: isDanger
    });
});
</script>
@endsection
