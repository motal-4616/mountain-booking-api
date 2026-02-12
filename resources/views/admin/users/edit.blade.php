@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Người dùng')
@section('page-title', 'Chỉnh sửa Người dùng')

@section('content')
<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil me-2"></i>Thông tin Người dùng
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control form-control-modern @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required placeholder="Nhập họ và tên">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control form-control-modern @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required placeholder="example@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Số điện thoại
                            </label>
                            <input type="text" name="phone" class="form-control form-control-modern @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}" placeholder="0912345678">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Mật khẩu mới
                            </label>
                            <input type="password" name="password" class="form-control form-control-modern @error('password') is-invalid @enderror" 
                                   placeholder="Để trống nếu không đổi">
                            <small class="text-muted">Chỉ nhập nếu muốn thay đổi mật khẩu</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select form-control-modern @error('role') is-invalid @enderror" required
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User - Người dùng thường</option>
                                @if(auth()->user()->role === 'super_admin')
                                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin - Quản trị viên</option>
                                    <option value="booking_manager" {{ old('role', $user->role) == 'booking_manager' ? 'selected' : '' }}>Booking Manager - Quản lý đặt vé</option>
                                    <option value="content_manager" {{ old('role', $user->role) == 'content_manager' ? 'selected' : '' }}>Content Manager - Quản lý nội dung</option>
                                @endif
                            </select>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Bạn không thể thay đổi vai trò của chính mình
                                </small>
                            @endif
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>User:</strong> Có thể đặt tour và quản lý đặt vé của mình.<br>
                                    @if(auth()->user()->role === 'super_admin')
                                        <strong>Super Admin:</strong> Toàn quyền quản trị hệ thống, bao gồm quản lý mã giảm giá.<br>
                                        <strong>Booking Manager:</strong> Quản lý đặt vé, xác nhận thanh toán.<br>
                                        <strong>Content Manager:</strong> Quản lý tour, đánh giá, nội dung website.
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-modern mb-3">
                <div class="card-header-modern">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Hành động
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-modern btn-primary-modern">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-modern btn-secondary-modern">
                            <i class="bi bi-eye me-2"></i>Xem chi tiết
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-modern btn-secondary-modern">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Thông tin
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="mb-3">
                        <small class="text-muted">Ngày tạo</small>
                        <div class="fw-semibold">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Cập nhật lần cuối</small>
                        <div class="fw-semibold">{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <small class="text-muted">Tổng đặt vé</small>
                        <div class="fw-semibold">{{ $user->bookings->count() }} đơn</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
