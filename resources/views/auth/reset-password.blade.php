@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="auth-page">
    <div class="auth-background">
        <div class="auth-gradient"></div>
    </div>
    
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="auth-logo">
                            @if(file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" alt="MountainBook">
                            @else
                                <i class="bi bi-mountains"></i>
                            @endif
                        </div>
                        <h2>Đặt lại mật khẩu</h2>
                        <p>Nhập mật khẩu mới cho tài khoản của bạn</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div class="form-group-modern mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-2 text-primary"></i>Email
                            </label>
                            <input type="email"
                                   class="form-control form-control-modern"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $email) }}"
                                   placeholder="Nhập email của bạn"
                                   required
                                   readonly>
                        </div>

                        <!-- New Password -->
                        <div class="form-group-modern mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-2 text-primary"></i>Mật khẩu mới
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control form-control-modern @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)"
                                       required
                                       autofocus>
                                <button type="button" class="btn-toggle-password" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-toggle-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group-modern mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-2 text-primary"></i>Xác nhận mật khẩu
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control form-control-modern"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Nhập lại mật khẩu mới"
                                       required>
                                <button type="button" class="btn-toggle-password" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="password_confirmation-toggle-icon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-auth btn-auth-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Đặt lại mật khẩu
                        </button>
                    </form>

                    <div class="auth-footer mt-4">
                        <p><a href="{{ route('login') }}" class="auth-link"><i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-toggle-icon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endsection
