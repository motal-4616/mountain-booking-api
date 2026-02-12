@extends('admin.layouts.app')

@section('title', 'Quản lý Đánh giá')
@section('page-title', 'Quản lý Đánh giá')

@section('content')
<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['total'] }}</div>
            <div class="stat-card-label">Tổng đánh giá</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['pending'] }}</div>
            <div class="stat-card-label">Chờ duyệt</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $stats['approved'] }}</div>
            <div class="stat-card-label">Đã duyệt</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</div>
            <div class="stat-card-label">Đánh giá TB</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" 
                       name="search" 
                       class="form-control form-control-modern" 
                       placeholder="Tên tour, người dùng, nội dung..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-flag me-1"></i>Trạng thái
                </label>
                <select name="status" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-star me-1"></i>Số sao
                </label>
                <select name="rating" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-search me-2"></i>Tìm kiếm
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-modern btn-outline-modern">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reviews Table -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách đánh giá</h5>
    </div>
    <div class="card-body-modern p-0">
        @if($reviews->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-star display-1 text-muted"></i>
                <h5 class="text-muted mt-3">Chưa có đánh giá nào</h5>
            </div>
        @else
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="8%">ID</th>
                            <th width="20%">Người dùng</th>
                            <th width="25%">Tour</th>
                            <th width="12%">Đánh giá</th>
                            <th width="12%">Trạng thái</th>
                            <th width="10%">Ngày tạo</th>
                            <th width="8%">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                        <tr>
                            <td>
                                <input type="checkbox" name="review_ids[]" value="{{ $review->id }}" class="form-check-input review-checkbox">
                            </td>
                                <td><span class="badge bg-secondary">#{{ $review->id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $review->user->avatar_url ?? asset('images/default-avatar.png') }}" 
                                         alt="{{ $review->user->name }}" 
                                         class="rounded-circle"
                                         width="36" height="36"
                                         style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $review->user->name }}</div>
                                        <small class="text-muted">{{ $review->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('tours.show', $review->tour_id) }}" 
                                   class="text-decoration-none fw-semibold text-primary">
                                    {{ Str::limit($review->tour->name, 40) }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <i class="bi bi-star-fill text-warning"></i>
                                        @else
                                            <i class="bi bi-star text-muted"></i>
                                        @endif
                                    @endfor
                                    <span class="ms-1 fw-semibold">{{ $review->rating }}</span>
                                </div>
                            </td>
                            <td>{!! $review->status_badge !!}</td>
                            <td>
                                <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="dropdown position-static">
                                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="window">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.reviews.show', $review->id) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Xem chi tiết
                                            </a>
                                        </li>
                                        @if($review->status !== 'approved')
                                        <li>
                                            <form action="{{ route('admin.reviews.updateStatus', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-check-circle me-2"></i>Duyệt
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @if($review->status !== 'rejected')
                                        <li>
                                            <form action="{{ route('admin.reviews.updateStatus', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="bi bi-x-circle me-2"></i>Từ chối
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" 
                                                  method="POST"
                                                  class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger btn-delete">
                                                    <i class="bi bi-trash me-2"></i>Xóa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <!-- Footer with Bulk Actions & Pagination -->
        @if(!$reviews->isEmpty())
        <div class="card-footer-modern d-flex justify-content-between align-items-center">
            <div>
                <form method="POST" action="{{ route('admin.reviews.bulk-action') }}" id="bulkActionForm" class="d-flex gap-2 align-items-center">
                    @csrf
                    <select name="action" class="form-select form-control-modern" style="width: auto;" required>
                        <option value="">Chọn hành động</option>
                        <option value="approve">Duyệt</option>
                        <option value="reject">Từ chối</option>
                        <option value="delete">Xóa</option>
                    </select>
                    <button type="submit" class="btn btn-modern btn-primary-modern btn-sm" id="bulkActionBtn" disabled>
                        <i class="bi bi-check2-all me-1"></i>Áp dụng
                    </button>
                    <span class="text-muted small ms-2" id="selectedCount">Đã chọn: 0</span>
                </form>
            </div>
            <div>
                {{ $reviews->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');

    // Select all checkbox
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }

    // Individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checked = document.querySelectorAll('.review-checkbox:checked');
        selectedCount.textContent = `Đã chọn: ${checked.length}`;
        bulkActionBtn.disabled = checked.length === 0;
    }

    // Bulk action form submission
    if (bulkActionForm) {
        bulkActionForm.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.review-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                Toast.warning('Vui lòng chọn ít nhất 1 đánh giá');
                return false;
            }

            const action = this.querySelector('select[name="action"]').value;
            if (!action) {
                e.preventDefault();
                Toast.warning('Vui lòng chọn hành động');
                return false;
            }

            e.preventDefault();
            const form = this;
            const count = checked.length;
            const isDanger = action === 'delete';
            const confirmMsg = action === 'delete' 
                ? `Bạn có chắc muốn <strong>xóa ${count} đánh giá</strong>?<br><small class="text-danger">Hành động này không thể hoàn tác!</small>`
                : `Bạn có chắc muốn <strong>${action === 'approve' ? 'duyệt' : 'từ chối'} ${count} đánh giá</strong>?`;

            confirmAction(confirmMsg, function() {
                // Add selected review IDs to form
                const existingInputs = form.querySelectorAll('input[name="review_ids[]"]');
                existingInputs.forEach(input => input.remove());
                
                checked.forEach(cb => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'review_ids[]';
                    hiddenInput.value = cb.value;
                    form.appendChild(hiddenInput);
                });
                form.submit();
            }, {
                title: isDanger ? 'Xác nhận xóa' : 'Xác nhận thao tác',
                confirmText: isDanger ? 'Xóa' : 'Xác nhận',
                isDanger: isDanger
            });
        });
    }

    // Delete single review
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            confirmDelete('Bạn có chắc muốn xóa đánh giá này?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>', function() {
                form.submit();
            });
        });
    });
});
</script>
@endsection
