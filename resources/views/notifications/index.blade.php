@extends('layouts.app')

@section('title', 'Thông báo')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-bell me-2"></i>Thông báo
                    @if($unreadCount > 0)
                        <span class="badge bg-danger">{{ $unreadCount }} mới</span>
                    @endif
                </h2>
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-check-all me-1"></i>Đánh dấu tất cả đã đọc
                        </button>
                    </form>
                @endif
            </div>

            @if($notifications->count() > 0)
                <div class="card shadow-sm">
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                            @endphp
                            <div class="list-group-item {{ $isUnread ? 'bg-light' : '' }}">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon me-3">
                                        <span class="rounded-circle bg-{{ $data['color'] ?? 'primary' }} bg-opacity-10 p-2 d-inline-flex">
                                            <i class="bi {{ $data['icon'] ?? 'bi-bell' }} text-{{ $data['color'] ?? 'primary' }}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1 {{ $isUnread ? 'fw-bold' : '' }}">
                                                {{ $data['title'] ?? 'Thông báo' }}
                                            </h6>
                                            <small class="text-muted">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <p class="mb-2 text-muted">{{ $data['message'] ?? '' }}</p>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(isset($data['url']))
                                                <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Xem chi tiết
                                                </a>
                                            @endif
                                            @if($isUnread)
                                                <form action="{{ route('notifications.markRead', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-check me-1"></i>Đã đọc
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 64px;"></i>
                        <h5 class="mt-3 text-muted">Không có thông báo nào</h5>
                        <p class="text-muted">Các thông báo mới sẽ hiển thị ở đây.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
