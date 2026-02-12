<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Mountain Booking Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Modern Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin-modern.css') }}">
    <!-- Toast Notification CSS -->
    <link rel="stylesheet" href="{{ asset('css/toast-notification.css') }}?v={{ time() }}">

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Mountain Booking" class="sidebar-logo">
                @else
                    <i class="bi bi-mountains"></i>
                @endif
            </div>
            <div class="sidebar-brand-text">MountainBook</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Tổng quan</div>
            
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
            </div>

            <div class="nav-section-title">Quản lý</div>

            @if(auth()->user()->canManageTours())
            <div class="nav-item">
                <a href="{{ route('admin.tours.index') }}" class="nav-link {{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">
                    <i class="bi bi-compass-fill"></i> Tour leo núi
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.schedules.index') }}" class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event-fill"></i> Lịch trình
                </a>
            </div>
            @endif

            @if(auth()->user()->canManageBookings())
            <div class="nav-item">
                <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated-fill"></i> Đơn đặt vé
                    @if(isset($pendingBookingsCount) && $pendingBookingsCount > 0)
                        <span class="nav-link-badge">{{ $pendingBookingsCount }}</span>
                    @endif
                </a>
            </div>
            @endif

            @if(auth()->user()->isSuperAdmin())
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Người dùng
                </a>
            </div>
            @endif

            @if(auth()->user()->canManageReviews())
            <div class="nav-item">
                <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="bi bi-star-fill"></i> Đánh giá
                    @php
                        $pendingReviewsCount = \App\Models\Review::where('status', 'pending')->count();
                    @endphp
                    @if($pendingReviewsCount > 0)
                        <span class="nav-link-badge">{{ $pendingReviewsCount }}</span>
                    @endif
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-fill"></i> Liên hệ
                </a>
            </div>
            @endif

            @if(auth()->user()->canManageCoupons())
            <div class="nav-item">
                <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-fill"></i> Mã giảm giá
                </a>
            </div>
            @endif

            <div class="nav-section-title">Phân tích</div>

            <div class="nav-item">
                <a href="{{ route('admin.revenue.index') }}" class="nav-link {{ request()->routeIs('admin.revenue.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> Doanh thu
                </a>
            </div>

            <div class="nav-section-title">Khác</div>

            <div class="nav-item">
                <a href="{{ route('home') }}" class="nav-link" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Xem Website
                </a>
            </div>

            <div class="nav-item">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-left"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="admin-topbar">
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            <div class="topbar-right">
                <div class="topbar-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Tìm kiếm...">
                </div>
                
                <!-- Notification Dropdown -->
                <div class="dropdown notification-dropdown">
                    <button class="topbar-icon-btn" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill"></i>
                        <span class="badge" id="notificationBadge" style="display: none;">0</span>
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationDropdown">
                        <div class="notification-header">
                            <h6 class="mb-0">Thông báo</h6>
                            <button class="btn btn-sm btn-link text-primary p-0" id="markAllReadBtn">
                                Đánh dấu tất cả
                            </button>
                        </div>
                        
                        <div class="notification-list" id="notificationList">
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                <small>Không có thông báo mới</small>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-link w-100">
                                Xem tất cả thông báo
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="topbar-user">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff" alt="User Avatar">
                    <div class="topbar-user-info">
                        <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                        <div class="topbar-user-role">Quản trị viên</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Content -->
        <div class="admin-content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toast Notification JS -->
    <script src="{{ asset('js/toast-notification.js') }}?v={{ time() }}"></script>
    
    <!-- Toast Messages - PHẢI sau khi load toast-notification.js -->
    @include('components.toast-messages')
    
    <!-- Notification System -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationBadge = document.getElementById('notificationBadge');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        
        // Hàm load notifications
        function loadNotifications() {
            fetch('{{ route("admin.notifications.latest") }}')
                .then(response => response.json())
                .then(data => {
                    updateBadge(data.unread_count);
                    renderNotifications(data.notifications);
                })
                .catch(error => console.error('Error loading notifications:', error));
        }
        
        // Cập nhật badge
        function updateBadge(count) {
            if (count > 0) {
                notificationBadge.textContent = count > 99 ? '99+' : count;
                notificationBadge.style.display = 'inline-block';
            } else {
                notificationBadge.style.display = 'none';
            }
        }
        
        // Render notifications
        function renderNotifications(notifications) {
            if (notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                        <small>Không có thông báo mới</small>
                    </div>
                `;
                return;
            }
            
            notificationList.innerHTML = notifications.map(notif => `
                <div class="notification-item ${notif.read_at ? 'read' : 'unread'}" data-id="${notif.id}">
                    <div class="notification-icon bg-${notif.color}">
                        <i class="${notif.icon}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notif.title}</div>
                        <div class="notification-message">${notif.message}</div>
                        <div class="notification-time">${notif.created_at}</div>
                    </div>
                    ${!notif.read_at ? '<span class="unread-dot"></span>' : ''}
                </div>
            `).join('');
            
            // Add click handlers
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const id = this.dataset.id;
                    markAsRead(id);
                    
                    // Navigate to URL if exists
                    const notif = notifications.find(n => n.id === id);
                    if (notif && notif.url && notif.url !== '#') {
                        window.location.href = notif.url;
                    }
                });
            });
        }
        
        // Đánh dấu đã đọc
        function markAsRead(id) {
            fetch(`{{ url('admin/notifications') }}/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.unread_count);
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error marking as read:', error));
        }
        
        // Đánh dấu tất cả đã đọc
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                fetch('{{ route("admin.notifications.markAllRead") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBadge(0);
                        loadNotifications();
                    }
                })
                .catch(error => console.error('Error marking all as read:', error));
            });
        }
        
        // Load notifications on page load
        loadNotifications();
        
        // Refresh every 30 seconds
        setInterval(loadNotifications, 30000);
    });
    </script>
    
    <!-- Form Submit Handler - Ngăn chặn double submit -->
    <script src="{{ asset('js/form-submit-handler.js') }}"></script>

    @stack('scripts')
</body>
</html>
