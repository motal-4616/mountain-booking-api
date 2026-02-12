@extends('admin.layouts.app')

@section('title', 'Quản lý Liên hệ')
@section('page-title', 'Quản lý Liên hệ')

@section('content')
<!-- Filter Bar -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form action="{{ route('admin.contacts.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Tìm kiếm
                </label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control form-control-modern" 
                       placeholder="Tìm theo tên, SĐT, email...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-flag me-1"></i>Trạng thái
                </label>
                <select name="status" class="form-select form-control-modern">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tất cả</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-modern btn-secondary-modern">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
                @if($unreadCount > 0)
                    <div class="badge-modern badge-danger" style="padding: 10px 16px; font-size: 13px;">
                        <i class="bi bi-envelope-fill me-1"></i>{{ $unreadCount }} chưa đọc
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card-modern">
    <div class="card-header-modern">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách tin nhắn</h5>
    </div>
    <div class="card-body-modern p-0">
        <div class="table-responsive">
            <table class="table-modern">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Khách hàng</th>
                <th>Liên hệ</th>
                <th>Nội dung</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th width="120">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td><span class="badge-modern badge-primary">#{{ $contact->id }}</span></td>
                    <td>
                        <div class="fw-semibold">{{ $contact->name }}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <a href="tel:{{ $contact->phone }}" class="text-decoration-none small">
                                <i class="bi bi-telephone text-primary"></i> {{ $contact->phone }}
                            </a>
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-decoration-none small">
                                    <i class="bi bi-envelope text-primary"></i> {{ $contact->email }}
                                </a>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($contact->message)
                            <div class="text-truncate" style="max-width: 300px;" title="{{ $contact->message }}">
                                <small>{{ $contact->message }}</small>
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="text-nowrap">
                            <div class="fw-medium">{{ $contact->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $contact->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                    <td>
                        @if($contact->status == 'unread')
                            <span class="badge-modern badge-warning">
                                <i class="bi bi-envelope-fill"></i> Chưa đọc
                            </span>
                        @else
                            <span class="badge-modern badge-success">
                                <i class="bi bi-envelope-open"></i> Đã đọc
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons-modern">
                            @if($contact->status == 'unread')
                                <button onclick="markAsRead({{ $contact->id }})" class="btn-action-outline btn-action-success" title="Đánh dấu đã đọc">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            @endif
                            <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action-outline btn-action-danger btn-delete" title="Xóa" data-name="{{ $contact->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted">Chưa có tin nhắn liên hệ nào</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
</div>

@if($contacts->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $contacts->links() }}
</div>
@endif



<script>
function markAsRead(id) {
    fetch(`/admin/contacts/${id}/mark-read`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.success('Đã đánh dấu là đã đọc');
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        Toast.error('Có lỗi xảy ra');
    });
}

// Delete confirmation
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const name = this.dataset.name || 'tin nhắn này';
        
        confirmDelete(`Xác nhận xóa tin nhắn từ <strong>${name}</strong>?`, function() {
            form.submit();
        });
    });
});
</script>
@endsection
