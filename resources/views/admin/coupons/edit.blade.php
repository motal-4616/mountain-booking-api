@extends('admin.layouts.app')

@section('title', 'Sửa mã giảm giá')
@section('page-title', 'Chỉnh Sửa Mã Giảm Giá')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-pencil me-2"></i>Chỉnh sửa mã: <code>{{ $coupon->code }}</code></h5>
            </div>
            <div class="card-body-modern">
                <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã giảm giá <span class="text-danger">*</span></label>
                            <input type="text" name="code" 
                                   class="form-control form-control-modern text-uppercase @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $coupon->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên mã <span class="text-danger">*</span></label>
                            <input type="text" name="name" 
                                   class="form-control form-control-modern @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $coupon->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" rows="2" 
                                  class="form-control form-control-modern @error('description') is-invalid @enderror">{{ old('description', $coupon->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại giảm giá <span class="text-danger">*</span></label>
                            <select name="type" id="couponType" 
                                    class="form-select form-control-modern @error('type') is-invalid @enderror" required>
                                <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                                <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Số tiền cố định (VNĐ)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giá trị giảm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="value" 
                                       class="form-control form-control-modern @error('value') is-invalid @enderror" 
                                       value="{{ old('value', $coupon->value) }}" 
                                       min="1" step="1" required>
                                <span class="input-group-text" id="valueUnit">{{ $coupon->type == 'percent' ? '%' : 'VNĐ' }}</span>
                            </div>
                            @error('value')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn hàng tối thiểu</label>
                            <div class="input-group">
                                <input type="number" name="min_order_amount" 
                                       class="form-control form-control-modern @error('min_order_amount') is-invalid @enderror" 
                                       value="{{ old('min_order_amount', $coupon->min_order_amount) }}" 
                                       min="0" step="1000">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="maxDiscountGroup">
                            <label class="form-label fw-semibold">Giảm tối đa</label>
                            <div class="input-group">
                                <input type="number" name="max_discount" 
                                       class="form-control form-control-modern @error('max_discount') is-invalid @enderror" 
                                       value="{{ old('max_discount', $coupon->max_discount) }}" 
                                       min="0" step="1000">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            @error('max_discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" 
                                   class="form-control form-control-modern @error('start_date') is-invalid @enderror" 
                                   value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" 
                                   class="form-control form-control-modern @error('end_date') is-invalid @enderror" 
                                   value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giới hạn lượt sử dụng</label>
                            <input type="number" name="usage_limit" 
                                   class="form-control form-control-modern @error('usage_limit') is-invalid @enderror" 
                                   value="{{ old('usage_limit', $coupon->usage_limit) }}" 
                                   min="1" placeholder="Không giới hạn">
                            <small class="text-muted">Đã sử dụng: {{ $coupon->used_count }} lượt</small>
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="isActive" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Kích hoạt
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart me-2"></i>Thống kê sử dụng</h5>
            </div>
            <div class="card-body-modern">
                <div class="d-flex justify-content-between mb-2">
                    <span>Đã sử dụng:</span>
                    <strong>{{ $coupon->used_count }} lượt</strong>
                </div>
                @if($coupon->usage_limit)
                <div class="d-flex justify-content-between mb-2">
                    <span>Còn lại:</span>
                    <strong>{{ $coupon->remaining_usage }} lượt</strong>
                </div>
                <div class="progress mb-3">
                    @php $usagePercent = min(100, ($coupon->used_count / $coupon->usage_limit) * 100); @endphp
                    <div class="progress-bar" role="progressbar" style="width: {{ $usagePercent }}%">
                        {{ round($usagePercent) }}%
                    </div>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span>Trạng thái:</span>
                    <span class="badge {{ $coupon->status_badge_class }}">{{ $coupon->status_text }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Ngày tạo:</span>
                    <span>{{ $coupon->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('couponType');
    const valueUnit = document.getElementById('valueUnit');
    const maxDiscountGroup = document.getElementById('maxDiscountGroup');

    function toggleMaxDiscount() {
        if (typeSelect.value === 'percent') {
            valueUnit.textContent = '%';
            maxDiscountGroup.style.display = 'block';
        } else {
            valueUnit.textContent = 'VNĐ';
            maxDiscountGroup.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', toggleMaxDiscount);
    toggleMaxDiscount();
});
</script>
@endpush
@endsection
