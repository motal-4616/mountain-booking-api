@extends('layouts.app')

@section('title', 'Đăng nhập')

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
                        <h2>Đăng nhập</h2>
                        <p>Chào mừng bạn trở lại MountainBook!</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="auth-form">
                        @csrf

                        <!-- Email -->
                        <div class="form-group-modern mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-2 text-primary"></i>Email
                            </label>
                            <input type="email"
                                   class="form-control form-control-modern @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Nhập email của bạn"
                                   required
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback d-block">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group-modern mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-2 text-primary"></i>Mật khẩu
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control form-control-modern @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Nhập mật khẩu"
                                       required>
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

                        <!-- Remember Me -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-auth btn-auth-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>hoặc</span>
                    </div>

                    <div class="auth-footer">
                        <p>Chưa có tài khoản? <a href="{{ route('register') }}" class="auth-link">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    position: relative;
    min-height: 100vh;
}

.auth-background {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    z-index: -1;
}

.auth-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.3) 0%, transparent 50%);
}

.auth-card {
    background: white;
    border-radius: 24px;
    padding: 48px 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: 40px;
}

.auth-logo {
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;
    background: white;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #10b981;
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
    padding: 20px;
}

.auth-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.auth-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.auth-header p {
    color: #64748b;
    font-size: 15px;
}

.btn-toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 8px;
    font-size: 18px;
    transition: color 0.2s;
}

.btn-toggle-password:hover {
    color: #10b981;
}

.btn-auth {
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-auth-primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(16, 185, 129, 0.5);
}

.auth-divider {
    position: relative;
    text-align: center;
    margin: 32px 0;
}

.auth-divider::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    width: 100%;
    height: 1px;
    background: #e2e8f0;
}

.auth-divider span {
    position: relative;
    background: white;
    padding: 0 16px;
    color: #94a3b8;
    font-size: 14px;
}

.auth-footer {
    text-align: center;
}

.auth-footer p {
    color: #64748b;
    margin: 0;
    font-size: 15px;
}

.auth-link {
    color: #10b981;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.auth-link:hover {
    color: #059669;
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-toggle-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
