@extends('admin.layouts.app')

@section('title', 'Quản lý Mã giảm giá')
@section('page-title', 'Quản lý Mã Giảm Giá')

@section('content')
<!-- Header với filter -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" name="search" class="form-control form-control-modern" 
                       placeholder="Mã, tên coupon..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-tag me-1"></i>Loại
                </label>
                <select name="type" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="percent" {{ request('type') == 'percent' ? 'selected' : '' }}>Phần trăm</option>
                    <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-toggle-on me-1"></i>Trạng thái
                </label>
                <select name="status" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
            </div>
            <div class="col-md-3">
                @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-modern btn-secondary-modern me-2">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @endif
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-plus-circle me-1"></i>Thêm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách coupon -->
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-ticket-perforated"></i> Danh sách Mã Giảm Giá</h5>
        <span class="badge-modern badge-primary">{{ $coupons->total() }} mã</span>
    </div>
    <div class="card-body-modern p-0">
        @if($coupons->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã</th>
                            <th>Tên</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Sử dụng</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            <tr>
                                <td><span class="badge-modern badge-secondary">#{{ $coupon->id }}</span></td>
                                <td>
                                    <code class="badge-modern badge-code">{{ $coupon->code }}</code>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $coupon->name }}</div>
                                    @if($coupon->description)
                                        <small class="text-muted d-block mt-1">{{ Str::limit($coupon->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->type == 'percent')
                                        <span class="badge-modern badge-info">Phần trăm</span>
                                    @else
                                        <span class="badge-modern badge-success">Cố định</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $coupon->value_display }}</div>
                                    @if($coupon->type == 'percent' && $coupon->max_discount)
                                        <small class="text-muted">Tối đa: {{ number_format($coupon->max_discount, 0, ',', '.') }}đ</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <span class="fw-bold">{{ $coupon->used_count }}</span>
                                        @if($coupon->usage_limit)
                                            <span class="text-muted">/ {{ $coupon->usage_limit }}</span>
                                        @else
                                            <span class="text-muted">/ ∞</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-nowrap small">
                                        <div>{{ $coupon->start_date->format('d/m/Y') }}</div>
                                        <div class="text-muted">~ {{ $coupon->end_date->format('d/m/Y') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-modern {{ str_replace('bg-', 'badge-', $coupon->status_badge_class) }}">
                                        {{ $coupon->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-modern">
                                        <a href="{{ route('admin.coupons.show', $coupon) }}" 
                                           class="btn-action-outline btn-action-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" 
                                           class="btn-action-outline btn-action-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.coupons.toggleStatus', $coupon) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn-action-outline btn-action-{{ $coupon->is_active ? 'warning' : 'success' }}"
                                                    title="{{ $coupon->is_active ? 'Tắt' : 'Bật' }}">
                                                <i class="bi bi-{{ $coupon->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        @if($coupon->used_count == 0)
                                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" 
                                                  method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-action-outline btn-action-danger btn-delete" 
                                                        title="Xóa" data-name="{{ $coupon->code }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3">
                {{ $coupons->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-ticket-perforated text-muted" style="font-size: 48px;"></i>
                <p class="text-muted mt-3">Chưa có mã giảm giá nào.</p>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Tạo mã giảm giá đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const name = this.dataset.name || 'mã này';
        
        confirmDelete(`Bạn có chắc muốn xóa mã <strong>${name}</strong>?`, function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
