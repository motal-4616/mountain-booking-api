@extends('admin.layouts.app')

@section('title', 'Quản lý Tour')
@section('page-title', 'Quản lý Tour Leo Núi')

@section('content')
<!-- Header với filter -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" name="search" class="form-control form-control-modern" 
                       placeholder="Tên tour, địa điểm..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-speedometer2 me-1"></i>Độ khó
                </label>
                <select name="difficulty" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Dễ</option>
                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Khó</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-toggle-on me-1"></i>Trạng thái
                </label>
                <select name="status" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                @if(request()->hasAny(['search', 'difficulty', 'status']))
                    <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-secondary-modern w-100">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @else
                    <a href="{{ route('admin.tours.create') }}" class="btn btn-modern btn-primary-modern w-100">
                        <i class="bi bi-plus-circle me-1"></i>Thêm mới
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
                <span id="selectedCount" class="fw-semibold">0</span> tour đã chọn
            </div>
            <div class="col-md-6 text-end">
                <form id="bulkActionForm" method="POST" action="{{ route('admin.tours.bulk-action') }}" class="d-inline">
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

<!-- Danh sách tour -->
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-compass"></i> Danh sách Tour</h5>
        <span class="badge-modern badge-primary">{{ $tours->total() }} tour</span>
    </div>
    <div class="card-body-modern p-0">
        @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Chọn tất cả">
                            </th>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên tour</th>
                            <th>Địa điểm</th>
                            <th>Độ khó</th>
                            <th>Lịch trình</th>
                            <th>Trạng thái</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tours as $tour)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input tour-checkbox" value="{{ $tour->id }}">
                                </td>
                                <td><span class="fw-bold text-muted">#{{ $tour->id }}</span></td>
                                <td>
                                    @if($tour->image)
                                        <img src="{{ asset($tour->image) }}" width="70" height="50" 
                                             class="rounded-3 object-fit-cover shadow-sm" alt="{{ $tour->name }}">
                                    @else
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" 
                                             style="width:70px;height:50px">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $tour->name }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $tour->location }}
                                    </small>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary me-1"></i>
                                    {{ $tour->location }}
                                </td>
                                <td>
                                    @if($tour->difficulty == 'easy')
                                        <span class="badge-modern badge-success">
                                            <i class="bi bi-check-circle me-1"></i>Dễ
                                        </span>
                                    @elseif($tour->difficulty == 'medium')
                                        <span class="badge-modern badge-warning">
                                            <i class="bi bi-exclamation-circle me-1"></i>TB
                                        </span>
                                    @else
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-fire me-1"></i>Khó
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-modern badge-info">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $tour->schedules_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($tour->is_active)
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
                                        <a href="{{ route('admin.tours.show', $tour) }}" 
                                           class="btn btn-sm btn-outline-info rounded-pill" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tours.edit', $tour) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.tours.destroy', $tour) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete" 
                                                    title="Xóa" data-name="{{ $tour->name }}">
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

            @if($tours->hasPages())
                <div class="card-body-modern border-top">
                    {{ $tours->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-compass"></i>
                </div>
                <h3>Chưa có tour nào</h3>
                <p>Bắt đầu thêm tour đầu tiên cho hệ thống của bạn</p>
                <a href="{{ route('admin.tours.create') }}" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-plus-circle me-2"></i>Thêm tour đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// Select All functionality
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.tour-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.tour-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

// Update bulk actions bar visibility and count
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.tour-checkbox:checked');
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
    document.querySelectorAll('.tour-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Delete confirmation với Popup
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const name = this.dataset.name || 'tour này';
        
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
    
    const count = document.querySelectorAll('.tour-checkbox:checked').length;
    let message = '';
    let isDanger = false;
    
    if (action === 'activate') {
        message = `Bạn có chắc muốn <strong>kích hoạt ${count} tour</strong>?`;
    } else if (action === 'deactivate') {
        message = `Bạn có chắc muốn <strong>tạm dừng ${count} tour</strong>?`;
    } else if (action === 'delete') {
        message = `Bạn có chắc muốn <strong>XÓA ${count} tour</strong>?<br><small class="text-danger">Hành động này không thể hoàn tác!</small>`;
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
