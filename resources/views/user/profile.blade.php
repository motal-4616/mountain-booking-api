@extends('layouts.app')

@section('title', 'Trang cá nhân')

@section('content')
<div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-bg"></div>
        <div class="profile-header-content">
            <div class="container">
                <div class="profile-info">
                    <div class="profile-avatar-wrapper">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-avatar">
                        <button class="profile-avatar-edit" onclick="document.getElementById('avatarInput').click()">
                            <i class="bi bi-camera"></i>
                        </button>
                        <form id="avatarForm" action="{{ route('user.profile.avatar') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                            @csrf
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                        </form>
                    </div>
                    <div class="profile-details">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-email"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="profile-phone"><i class="bi bi-telephone me-2"></i>{{ $user->phone }}</p>
                        @endif
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <span class="stat-value">{{ $bookingsCount }}</span>
                                <span class="stat-label">Đơn đặt</span>
                            </div>
                            <div class="profile-stat">
                                <span class="stat-value">{{ $wishlistCount }}</span>
                                <span class="stat-label">Yêu thích</span>
                            </div>
                            <div class="profile-stat">
                                <span class="stat-value">{{ $user->created_at->format('d/m/Y') }}</span>
                                <span class="stat-label">Tham gia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="container">
            <div class="profile-grid">
                <!-- Left Column - Edit Form -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h3><i class="bi bi-person-gear me-2"></i>Thông tin cá nhân</h3>
                    </div>
                    <div class="profile-card-body">
                        <form action="{{ route('user.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group-modern">
                                <label><i class="bi bi-person"></i>Họ và tên</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="form-group-modern">
                                <label><i class="bi bi-telephone"></i>Số điện thoại</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                            </div>

                            <div class="form-group-modern">
                                <label><i class="bi bi-calendar-event"></i>Ngày sinh</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                            </div>

                            <div class="form-group-modern">
                                <label><i class="bi bi-geo-alt"></i>Địa chỉ</label>
                                <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Nhập địa chỉ của bạn">
                            </div>

                            <div class="form-group-modern">
                                <label><i class="bi bi-chat-text"></i>Giới thiệu bản thân</label>
                                <textarea name="bio" rows="3" placeholder="Viết vài dòng giới thiệu về bạn...">{{ old('bio', $user->bio) }}</textarea>
                            </div>

                            <button type="submit" class="btn-profile-save">
                                <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="profile-right">
                    <!-- Change Password -->
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h3><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h3>
                        </div>
                        <div class="profile-card-body">
                            <form action="{{ route('user.profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="form-group-modern">
                                    <label><i class="bi bi-key"></i>Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password" required>
                                </div>

                                <div class="form-group-modern">
                                    <label><i class="bi bi-lock"></i>Mật khẩu mới</label>
                                    <input type="password" name="password" required>
                                </div>

                                <div class="form-group-modern">
                                    <label><i class="bi bi-lock-fill"></i>Xác nhận mật khẩu</label>
                                    <input type="password" name="password_confirmation" required>
                                </div>

                                <button type="submit" class="btn-profile-save btn-outline">
                                    <i class="bi bi-arrow-repeat me-2"></i>Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h3><i class="bi bi-ticket-perforated me-2"></i>Đặt vé gần đây</h3>
                            <a href="{{ route('bookings.index') }}" class="view-all-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="profile-card-body">
                            @if($recentBookings->count() > 0)
                                <div class="booking-list-mini">
                                    @foreach($recentBookings as $booking)
                                        <div class="booking-item-mini">
                                            <div class="booking-item-info">
                                                <strong>{{ $booking->schedule->tour->name ?? 'N/A' }}</strong>
                                                <span>{{ $booking->schedule->departure_date?->format('d/m/Y') ?? 'N/A' }}</span>
                                            </div>
                                            <span class="booking-status status-{{ $booking->status }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state-mini">
                                    <i class="bi bi-ticket"></i>
                                    <p>Chưa có đơn đặt nào</p>
                                    <a href="{{ route('tours.index') }}" class="btn-sm-primary">Khám phá Tour</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    min-height: 100vh;
    background: var(--light);
}

.profile-header {
    position: relative;
    padding-top: 60px;
}

.profile-header-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 280px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}

.profile-header-content {
    position: relative;
    z-index: 1;
    padding-bottom: 40px;
}

.profile-info {
    display: flex;
    align-items: flex-end;
    gap: 32px;
}

.profile-avatar-wrapper {
    position: relative;
}

.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 6px solid var(--white);
    box-shadow: var(--shadow-lg);
    object-fit: cover;
}

.profile-avatar-edit {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--primary);
    color: var(--white);
    border: 3px solid var(--white);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.profile-avatar-edit:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

.profile-details {
    padding-bottom: 20px;
}

.profile-details h1 {
    font-size: 32px;
    font-weight: 800;
    color: var(--white);
    margin-bottom: 8px;
}

.profile-email, .profile-phone {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 4px;
}

.profile-stats {
    display: flex;
    gap: 32px;
    margin-top: 16px;
}

.profile-stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: var(--white);
}

.stat-label {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.8);
}

.profile-content {
    padding: 40px 20px 80px;
}

.profile-content .container {
    max-width: 1200px;
    margin: 0 auto;
}

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
}

.profile-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.profile-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--gray-light);
}

.profile-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
    display: flex;
    align-items: center;
    margin: 0;
}

.profile-card-header h3 i {
    color: var(--primary);
}

.view-all-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.view-all-link:hover {
    text-decoration: underline;
}

.profile-card-body {
    padding: 24px;
}

.btn-profile-save {
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: var(--white);
    border: none;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-profile-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
}

.btn-profile-save.btn-outline {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
}

.btn-profile-save.btn-outline:hover {
    background: var(--primary);
    color: var(--white);
}

.profile-right {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.booking-list-mini {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.booking-item-mini {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: var(--light);
    border-radius: var(--radius-sm);
}

.booking-item-info strong {
    display: block;
    font-size: 14px;
    color: var(--dark);
}

.booking-item-info span {
    font-size: 13px;
    color: var(--gray);
}

.booking-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-confirmed { background: #d1fae5; color: #059669; }
.status-paid { background: #dbeafe; color: #2563eb; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.empty-state-mini {
    text-align: center;
    padding: 32px 16px;
}

.empty-state-mini i {
    font-size: 40px;
    color: var(--gray-light);
    margin-bottom: 12px;
}

.empty-state-mini p {
    color: var(--gray);
    margin-bottom: 16px;
}

.btn-sm-primary {
    padding: 8px 20px;
    background: var(--primary);
    color: var(--white);
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
    display: inline-block;
}

.btn-sm-primary:hover {
    background: var(--primary-dark);
}

.alert-modern {
    padding: 16px 20px;
    border-radius: var(--radius-md);
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.alert-success {
    background: #d1fae5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.alert-danger {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

@media (max-width: 992px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
    }
    
    .profile-stats {
        justify-content: center;
    }
}
</style>
@endsection
