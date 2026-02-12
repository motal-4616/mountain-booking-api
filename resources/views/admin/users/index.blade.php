@extends('admin.layouts.app')

@section('title', 'Quản lý Người dùng')
@section('page-title', 'Quản lý Người Dùng')

@section('content')
<!-- Header với filter -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" name="search" class="form-control form-control-modern" 
                       placeholder="Tìm tên, email, số điện thoại..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-shield me-1"></i>Quyền
                </label>
                <select name="role" class="form-select form-control-modern">
                    <option value="">Tất cả</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-modern btn-secondary-modern w-100">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @else
                    <a href="{{ route('admin.users.create') }}" class="btn btn-modern btn-primary-modern w-100">
                        <i class="bi bi-plus-circle me-1"></i>Thêm
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Danh sách người dùng -->
<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-people"></i> Danh sách Người dùng</h5>
        <span class="badge-modern badge-primary">{{ $users->total() }} người dùng</span>
    </div>
    <div class="card-body-modern p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thông tin</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Quyền</th>
                            <th>Số đơn</th>
                            <th>Ngày tạo</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td><span class="fw-bold text-muted">#{{ $user->id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle" style="width:40px;height:40px;background:linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            @if($user->id === auth()->id())
                                                <span class="badge-modern badge-info" style="font-size:10px;padding:2px 8px;">Bạn</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="bi bi-envelope text-muted me-1"></i>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    @if($user->phone)
                                        <i class="bi bi-telephone text-muted me-1"></i>
                                        {{ $user->phone }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge-modern badge-danger">
                                            <i class="bi bi-shield-check me-1"></i>Admin
                                        </span>
                                    @else
                                        <span class="badge-modern badge-info">
                                            <i class="bi bi-person me-1"></i>User
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-modern badge-primary">
                                        <i class="bi bi-receipt me-1"></i>{{ $user->bookings_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-info rounded-pill" title="Xem">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete" 
                                                        title="Xóa" data-name="{{ $user->name }}">
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

            @if($users->hasPages())
                <div class="card-body-modern border-top">
                    {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h3>Không tìm thấy người dùng nào</h3>
                <p>Thử thay đổi điều kiện lọc hoặc thêm người dùng mới</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-plus-circle me-2"></i>Thêm người dùng
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
        const name = this.dataset.name || 'người dùng này';
        
        confirmDelete(`Bạn có chắc muốn xóa <strong>${name}</strong>?<br><small class="text-muted">Tất cả dữ liệu liên quan sẽ bị xóa.</small>`, function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
