@extends('admin.layouts.app')

@section('title', 'Sửa Lịch Trình')
@section('page-title', 'Sửa Lịch Trình')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-calendar-check"></i> Chỉnh sửa Lịch trình</h5>
            </div>
            <div class="card-body-modern">
                <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                    @csrf
                    @method('PUT')

                    <!-- Chọn Tour -->
                    <div class="mb-4">
                        <label for="tour_id" class="form-label fw-semibold">
                            <i class="bi bi-compass me-1 text-primary"></i>Chọn Tour <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-control-modern @error('tour_id') is-invalid @enderror" id="tour_id" name="tour_id" required>
                            @foreach($tours as $tour)
                                <option value="{{ $tour->id }}" {{ old('tour_id', $schedule->tour_id) == $tour->id ? 'selected' : '' }}>
                                    {{ $tour->name }} ({{ $tour->formatted_price }})
                                </option>
                            @endforeach
                        </select>
                        @error('tour_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ngày khởi hành -->
                    <div class="mb-4">
                        <label for="departure_date" class="form-label fw-semibold">
                            <i class="bi bi-calendar-event me-1 text-primary"></i>Ngày khởi hành <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-modern @error('departure_date') is-invalid @enderror"
                               id="departure_date" name="departure_date"
                               value="{{ old('departure_date', $schedule->departure_date->format('Y-m-d')) }}" required>
                        @error('departure_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ngày kết thúc -->
                    <div class="mb-4">
                        <label for="end_date" class="form-label fw-semibold">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>Ngày kết thúc
                        </label>
                        <input type="date" class="form-control form-control-modern @error('end_date') is-invalid @enderror"
                               id="end_date" name="end_date"
                               value="{{ old('end_date', $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '') }}">
                        <small class="text-muted">Để trống nếu tour 1 ngày</small>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Giá tour cho lịch trình này -->
                    <div class="mb-4">
                        <label for="price" class="form-label fw-semibold">
                            <i class="bi bi-currency-dollar me-1 text-primary"></i>Giá tour (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-modern @error('price') is-invalid @enderror"
                               id="price" name="price" value="{{ old('price', $schedule->price) }}" min="0" step="10000" required>
                        <small class="text-muted">Giá tiền cho mỗi người trong lịch trình này</small>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Số người tối đa -->
                    <div class="mb-4">
                        <label for="max_people" class="form-label fw-semibold">
                            <i class="bi bi-people me-1 text-primary"></i>Số người tối đa <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-modern @error('max_people') is-invalid @enderror"
                               id="max_people" name="max_people"
                               value="{{ old('max_people', $schedule->max_people) }}" min="1" required>
                        <div class="mt-2">
                            <span class="badge-modern badge-info me-2">
                                <i class="bi bi-person-check me-1"></i>Đã đặt: {{ $schedule->max_people - $schedule->available_slots }} người
                            </span>
                            <span class="badge-modern badge-success">
                                <i class="bi bi-check-circle me-1"></i>Còn trống: {{ $schedule->available_slots }} chỗ
                            </span>
                        </div>
                        @error('max_people')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Số người tối thiểu -->
                    <div class="mb-4">
                        <label for="min_people" class="form-label fw-semibold">
                            <i class="bi bi-people-fill me-1 text-warning"></i>Số người tối thiểu <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-modern @error('min_people') is-invalid @enderror"
                               id="min_people" name="min_people" value="{{ old('min_people', $schedule->min_people ?? 10) }}" min="1" required>
                        <small class="text-muted">Lịch trình sẽ tự động hủy nếu không đủ số người này</small>
                        @error('min_people')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Thời hạn đăng ký -->
                    <div class="mb-4">
                        <label for="registration_deadline_days" class="form-label fw-semibold">
                            <i class="bi bi-clock-history me-1 text-info"></i>Đóng đăng ký trước (ngày) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-modern @error('registration_deadline_days') is-invalid @enderror"
                               id="registration_deadline_days" name="registration_deadline_days" 
                               value="{{ old('registration_deadline_days', $schedule->registration_deadline_days ?? 2) }}" min="1" max="30" required>
                        <small class="text-muted">Số ngày trước khởi hành để đóng đăng ký và kiểm tra số người</small>
                        @error('registration_deadline_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Trạng thái -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $schedule->is_active) ? 'checked' : '' }} style="width: 50px; height: 26px;">
                            <label class="form-check-label fw-semibold ms-2" for="is_active">
                                Kích hoạt lịch trình
                            </label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-modern btn-primary-modern" id="update-btn">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-modern btn-secondary-modern">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Chống double submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const updateBtn = document.getElementById('update-btn');
    
    form.addEventListener('submit', function(e) {
        if (updateBtn.disabled) {
            e.preventDefault();
            return false;
        }
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    });
});
</script>
@endsection