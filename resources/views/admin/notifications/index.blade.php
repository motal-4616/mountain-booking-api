@extends('admin.layouts.app')

@section('title', 'Thông báo')
@section('page-title', 'Thông báo')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-2">Tất cả thông báo</h4>
                <p class="text-muted mb-0">
                    <span class="badge bg-primary">{{ $unreadCount }}</span> thông báo chưa đọc
                </p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.notifications.markAllRead') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-modern btn-primary-modern">
                        <i class="bi bi-check-all me-2"></i>Đánh dấu tất cả đã đọc
                    </button>
                </form>
                <form action="{{ route('admin.notifications.deleteAllRead') }}" method="POST" class="d-inline" id="deleteAllReadForm">
                    @csrf
                    <button type="button" class="btn btn-modern btn-danger-modern" id="btnDeleteAllRead">
                        <i class="bi bi-trash me-2"></i>Xóa đã đọc
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-body-modern p-0">
        @if($notifications->count() > 0)
            <div class="notification-list-page">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $icon = $data['icon'] ?? 'bi-bell';
                        $color = $data['color'] ?? 'primary';
                        $title = $data['title'] ?? 'Thông báo';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? '#';
                    @endphp
                    
                    <div class="notification-item-page {{ $notification->read_at ? 'read' : 'unread' }}">
                        <div class="notification-icon-page bg-{{ $color }}">
                            <i class="{{ $icon }}"></i>
                        </div>
                        
                        <div class="notification-content-page">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="notification-title-page mb-0">{{ $title }}</h6>
                                <small class="text-muted text-nowrap ms-3">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                            
                            <p class="notification-message-page mb-2">{{ $message }}</p>
                            
                            <div class="d-flex gap-2 align-items-center">
                                @if($url !== '#')
                                    <a href="{{ $url }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Xem chi tiết
                                    </a>
                                @endif
                                
                                @if(!$notification->read_at)
                                    <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check me-1"></i>Đánh dấu đã đọc
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        @if(!$notification->read_at)
                            <span class="unread-indicator"></span>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-top">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <h5>Không có thông báo</h5>
                <p class="text-muted">Bạn chưa có thông báo nào.</p>
            </div>
        @endif
    </div>
</div>

<style>
.notification-list-page {
    display: flex;
    flex-direction: column;
}

.notification-item-page {
    display: flex;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    position: relative;
}

.notification-item-page:hover {
    background: #f8fafc;
}

.notification-item-page.unread {
    background: #f0f9ff;
}

.notification-item-page.unread:hover {
    background: #e0f2fe;
}

.notification-item-page:last-child {
    border-bottom: none;
}

.notification-icon-page {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
    font-size: 20px;
}

.notification-icon-page.bg-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.notification-icon-page.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.notification-icon-page.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notification-icon-page.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.notification-icon-page.bg-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.notification-content-page {
    flex: 1;
    min-width: 0;
}

.notification-title-page {
    font-weight: 700;
    color: #1e293b;
    font-size: 15px;
}

.notification-message-page {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 0;
}

.unread-indicator {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 10px;
    height: 10px;
    background: #6366f1;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-state-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #94a3b8;
}

.empty-state h5 {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.empty-state p {
    color: #64748b;
    font-size: 14px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
}
</style>

@push('scripts')
<script>
// Xóa tất cả thông báo đã đọc
document.getElementById('btnDeleteAllRead')?.addEventListener('click', function() {
    const form = document.getElementById('deleteAllReadForm');
    confirmDelete('Bạn có chắc muốn xóa tất cả thông báo đã đọc?', function() {
        form.submit();
    });
});

// Xóa từng thông báo
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        confirmDelete('Bạn có chắc muốn xóa thông báo này?', function() {
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
