<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mountain Booking') - Đặt Vé Leo Núi</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Modern User CSS -->
    <link rel="stylesheet" href="{{ asset('css/user-modern.css') }}">
    <!-- Toast Notification CSS -->
    <link rel="stylesheet" href="{{ asset('css/toast-notification.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="modern-navbar" id="navbar">
        <div class="navbar-container">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Mountain Booking" class="navbar-logo">
                @else
                    <i class="bi bi-mountains"></i>
                @endif
                <span>MountainBook</span>
            </a>

            <ul class="navbar-menu">
                <li>
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        Trang chủ
                    </a>
                </li>
                <li>
                    <a href="{{ route('tours.index') }}" class="{{ request()->routeIs('tours.*') ? 'active' : '' }}">
                        Tour leo núi
                    </a>
                </li>
                <li>
                    <a href="#" onclick="openContactModal(); return false;">
                        Liên hệ
                    </a>
                </li>
            </ul>

            <!-- Search Bar -->
            <form action="{{ route('tours.index') }}" method="GET" class="navbar-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Tìm tour..." value="{{ request('search') }}">
            </form>

            <div class="navbar-actions">
                @auth
                    <!-- Wishlist -->
                    <a href="{{ route('user.wishlist') }}" class="navbar-icon-btn" title="Yêu thích">
                        <i class="bi bi-heart"></i>
                        <span class="icon-badge wishlist-count" style="display: {{ $wishlistCount > 0 ? 'flex' : 'none' }}">{{ $wishlistCount }}</span>
                    </a>
                    
                    <!-- Notifications -->
                    <button class="navbar-icon-btn" onclick="toggleNotifications()" title="Thông báo">
                        <i class="bi bi-bell"></i>
                        <span class="icon-badge notification-count">0</span>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="navbar-user dropdown">
                        <button class="navbar-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                            <span class="navbar-user-name">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small class="d-block text-muted">{{ auth()->user()->email }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('user.profile') }}">
                                    <i class="bi bi-person me-2"></i> Trang cá nhân
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bookings.index') }}">
                                    <i class="bi bi-ticket-perforated me-2"></i> Vé của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('user.wishlist') }}">
                                    <i class="bi bi-heart me-2"></i> Tour yêu thích
                                </a>
                            </li>
                            @if(auth()->user()->isAdmin())
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-warning" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-gear-fill me-2"></i> Admin Panel
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-outline">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn-nav-primary">Đăng ký</a>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Contact Modal - 2 Column Layout -->
    <div id="contactModal" class="contact-modal-overlay">
        <div class="contact-modal-container">
            <div class="contact-modal-content">
                <!-- Left Column - Contact Info -->
                <div class="contact-modal-info">
                    <div class="contact-info-header">
                        <div class="contact-info-icon">
                            @if(file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" alt="Mountain Booking Logo">
                            @else
                                <i class="bi bi-mountain"></i>
                            @endif
                        </div>
                        <h4>Mountain Booking</h4>
                        <p>Khám phá vẻ đẹp của những đỉnh núi hùng vĩ cùng chúng tôi</p>
                    </div>
                    
                    <div class="contact-info-list">
                        <div class="contact-info-item">
                            <div class="contact-info-item-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <strong>Địa chỉ</strong>
                                <span>123 Đường ABC, Quận 1, TP.HCM</span>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="contact-info-item-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <strong>Hotline</strong>
                                <a href="tel:0123456789">0123 456 789</a>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="contact-info-item-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <strong>Email</strong>
                                <a href="mailto:info@mountainbook.vn">info@mountainbook.vn</a>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="contact-info-item-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <strong>Giờ làm việc</strong>
                                <span>8:00 - 20:00 (T2 - CN)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-social">
                        <p>Theo dõi chúng tôi</p>
                        <div class="contact-social-links">
                            <a href="#"><i class="bi bi-facebook"></i></a>
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-youtube"></i></a>
                            <a href="#"><i class="bi bi-tiktok"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Contact Form -->
                <div class="contact-modal-form">
                    <button onclick="closeContactModal()" class="contact-modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div class="contact-modal-header">
                        <h3><i class="bi bi-chat-heart me-2"></i>Liên hệ tư vấn</h3>
                    </div>
                    <p class="contact-modal-desc">Để lại thông tin, chúng tôi sẽ liên hệ tư vấn cho bạn trong thời gian sớm nhất!</p>
                    
                    <form id="contactForm" action="{{ route('contact.store') }}" method="POST">
                        @csrf
                        <div class="form-group-modern">
                            <label><i class="bi bi-person"></i>Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" required placeholder="Nhập họ và tên">
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="bi bi-telephone"></i>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" required placeholder="0123 456 789">
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="bi bi-envelope"></i>Email</label>
                            <input type="email" name="email" placeholder="email@example.com">
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="bi bi-chat-dots"></i>Nội dung</label>
                            <textarea name="message" rows="3" placeholder="Bạn cần tư vấn về tour nào?"></textarea>
                        </div>
                        
                        <button type="submit" class="btn-contact-submit">
                            <i class="bi bi-send-fill me-2"></i>Gửi thông tin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notifications Panel -->
    <div id="notificationsPanel" class="notifications-panel">
        <div class="notifications-header">
            <h5><i class="bi bi-bell me-2"></i>Thông báo</h5>
            <button onclick="toggleNotifications()" class="notifications-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="notifications-body" id="notificationsList">
            <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <p>Chưa có thông báo mới</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="footer-content">
            <div class="footer-brand">
                <h3><i class="bi bi-mountains"></i> MountainBook</h3>
                <p>Khám phá những đỉnh núi hùng vĩ Việt Nam. Trải nghiệm những chuyến hành trình đáng nhớ cùng đội ngũ hướng dẫn viên chuyên nghiệp.</p>
                <div class="footer-social">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Liên kết nhanh</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('tours.index') }}">Tour leo núi</a></li>
                    <li><a href="#">Về chúng tôi</a></li>
                    <li><a href="#">Liên hệ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Hỗ trợ</h4>
                <ul class="footer-links">
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Hướng dẫn đặt vé</a></li>
                    <li><a href="#">Chính sách hoàn tiền</a></li>
                    <li><a href="#">Điều khoản dịch vụ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Liên hệ</h4>
                <ul class="footer-links">
                    <li><i class="bi bi-geo-alt-fill me-2"></i>123 Đường ABC, TP. HCM</li>
                    <li><i class="bi bi-telephone-fill me-2"></i>0123 456 789</li>
                    <li><i class="bi bi-envelope-fill me-2"></i>info@mountainbook.vn</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} MountainBook. Dự án học tập - IT Support Intern.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toast Notification JS -->
    <script src="{{ asset('js/toast-notification.js') }}?v={{ time() }}"></script>
    
    <!-- Toast Messages - PHẢI sau khi load toast-notification.js -->
    @include('components.toast-messages')
    
    <!-- Form Submit Handler - Ngăn chặn double submit -->
    <script src="{{ asset('js/form-submit-handler.js') }}"></script>

    <!-- Navbar Scroll Effect -->
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Contact Modal
        function openContactModal() {
            document.getElementById('contactModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeContactModal() {
            document.getElementById('contactModal').classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Notifications Panel
        function toggleNotifications() {
            const panel = document.getElementById('notificationsPanel');
            panel.classList.toggle('active');
            
            // Load notifications when opening
            if (panel.classList.contains('active')) {
                loadNotifications();
            }
        }
        
        // Load notifications via AJAX
        function loadNotifications() {
            fetch('{{ route("notifications.latest") }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('notificationsList');
                
                if (data.notifications.length === 0) {
                    container.innerHTML = `
                        <div class="notification-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>Chưa có thông báo mới</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = data.notifications.map(notif => `
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
                    
                    // Update badge count
                    updateNotificationCount(data.unread_count);
                    
                    // Add click handlers
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const id = this.dataset.id;
                            markAsRead(id);
                            
                            // Navigate to URL if exists
                            const notif = data.notifications.find(n => n.id == id);
                            if (notif && notif.url && notif.url !== '#') {
                                window.location.href = notif.url;
                            }
                        });
                    });
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
        }
        
        // Mark notification as read
        function markAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationCount(data.unread_count);
                    loadNotifications(); // Reload list
                }
            })
            .catch(error => console.error('Error marking as read:', error));
        }
        
        // Update notification count badge
        function updateNotificationCount(count) {
            const badge = document.querySelector('.notification-count');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        }
        
        // Load unread count on page load
        @auth
        fetch('{{ route("notifications.count") }}')
            .then(response => response.json())
            .then(data => updateNotificationCount(data.count))
            .catch(error => console.error('Error loading count:', error));
            
        // Poll for new notifications every 10 seconds (faster)
        setInterval(() => {
            fetch('{{ route("notifications.count") }}')
                .then(response => response.json())
                .then(data => updateNotificationCount(data.count))
                .catch(error => console.error('Error polling count:', error));
        }, 10000);
        @endauth
        
        // Close notifications when clicking outside
        document.addEventListener('click', function(e) {
            const panel = document.getElementById('notificationsPanel');
            const btn = e.target.closest('.navbar-icon-btn[onclick*="toggleNotifications"]');
            if (panel && panel.classList.contains('active') && !panel.contains(e.target) && !btn) {
                panel.classList.remove('active');
            }
        });
        
        // Wishlist Counter (from database)
        function updateWishlistCount() {
            @auth
            fetch('{{ route("user.wishlist.sync") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const badges = document.querySelectorAll('.wishlist-count');
                    badges.forEach(badge => {
                        badge.textContent = data.count;
                        badge.style.display = data.count > 0 ? 'flex' : 'none';
                    });
                }
            })
            .catch(err => console.error('Error fetching wishlist count:', err));
            @else
            // Guest users
            const badges = document.querySelectorAll('.wishlist-count');
            badges.forEach(badge => {
                badge.style.display = 'none';
            });
            @endauth
        }
        
        // Contact Form Submit
        document.addEventListener('DOMContentLoaded', function() {
            updateWishlistCount();
            
            const form = document.getElementById('contactForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    // Show loading
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';
                    submitBtn.disabled = true;
                    
                    // Send to server
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success
                            const formContainer = document.querySelector('.contact-modal-form');
                            formContainer.innerHTML = `
                                <div style="text-align: center; padding: 40px 20px;">
                                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%); border-radius: 50%; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-check-lg" style="font-size: 40px; color: white;"></i>
                                    </div>
                                    <h3 style="margin-bottom: 16px; color: var(--dark);">Gửi thành công!</h3>
                                    <p style="color: var(--gray); margin-bottom: 32px;">Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
                                    <button onclick="closeContactModal()" class="btn-contact-submit">Đóng</button>
                                </div>
                            `;
                            
                            setTimeout(() => {
                                closeContactModal();
                                form.reset();
                            }, 3000);
                        } else {
                            throw new Error(data.message || 'Có lỗi xảy ra');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại!');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }
        });
        
        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeContactModal();
                document.getElementById('notificationsPanel')?.classList.remove('active');
            }
        });
        
        // Close modals on backdrop click
        const contactModal = document.getElementById('contactModal');
        if (contactModal) {
            contactModal.addEventListener('click', function(e) {
                if (e.target === this) closeContactModal();
            });
        }
    </script>

    @stack('scripts')
</body>
</html>