@extends('admin.layouts.app')

@section('title', 'Thêm Người dùng')
@section('page-title', 'Thêm Người dùng Mới')

@section('content')
<form action="{{ route('admin.users.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>Thông tin Người dùng
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control form-control-modern @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required placeholder="Nhập họ và tên">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control form-control-modern @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required placeholder="example@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Số điện thoại
                            </label>
                            <input type="text" name="phone" class="form-control form-control-modern @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" placeholder="0912345678">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-control form-control-modern @error('password') is-invalid @enderror" 
                                   required placeholder="Tối thiểu 6 ký tự">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select form-control-modern @error('role') is-invalid @enderror" required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User - Người dùng thường</option>
                                @if(auth()->user()->role === 'super_admin')
                                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin - Quản trị viên</option>
                                    <option value="booking_manager" {{ old('role') == 'booking_manager' ? 'selected' : '' }}>Booking Manager - Quản lý đặt vé</option>
                                    <option value="content_manager" {{ old('role') == 'content_manager' ? 'selected' : '' }}>Content Manager - Quản lý nội dung</option>
                                @endif
                            </select>
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
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Hành động
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-modern btn-primary-modern">
                            <i class="bi bi-check-circle me-2"></i>Tạo người dùng
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-modern btn-secondary-modern">
                            <i class="bi bi-x-circle me-2"></i>Hủy bỏ
                        </a>
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-info-modern mb-0">
                        <div class="d-flex">
                            <i class="bi bi-lightbulb flex-shrink-0 me-2 mt-1"></i>
                            <div>
                                <strong>Lưu ý:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    <li>Email phải là duy nhất</li>
                                    <li>Mật khẩu tối thiểu 6 ký tự</li>
                                    <li>Người dùng sẽ nhận email kích hoạt</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
