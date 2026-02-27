@extends('admin.layouts.app')

@section('title', 'Tạo mã giảm giá')
@section('page-title', 'Tạo Mã Giảm Giá Mới')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-plus-circle me-2"></i>Thông tin mã giảm giá</h5>
            </div>
            <div class="card-body-modern">
                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã giảm giá <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="code" 
                                       class="form-control form-control-modern text-uppercase @error('code') is-invalid @enderror" 
                                       value="{{ old('code') }}" 
                                       placeholder="VD: SALE2026" required>
                                <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                    <i class="bi bi-shuffle"></i> Tạo ngẫu nhiên
                                </button>
                            </div>
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên mã <span class="text-danger">*</span></label>
                            <input type="text" name="name" 
                                   class="form-control form-control-modern @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="VD: Khuyến mãi Tết 2026" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" rows="2" 
                                  class="form-control form-control-modern @error('description') is-invalid @enderror"
                                  placeholder="Mô tả ngắn về mã giảm giá...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại giảm giá <span class="text-danger">*</span></label>
                            <select name="type" id="couponType" 
                                    class="form-select form-control-modern @error('type') is-invalid @enderror" required>
                                <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                                <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định (VNĐ)</option>
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
                                       value="{{ old('value') }}" 
                                       min="1" step="1" placeholder="10" required>
                                <span class="input-group-text" id="valueUnit">%</span>
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
                                       value="{{ old('min_order_amount', 0) }}" 
                                       min="0" step="1000" placeholder="0">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            <small class="text-muted">Để 0 nếu không giới hạn</small>
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="maxDiscountGroup">
                            <label class="form-label fw-semibold">Giảm tối đa</label>
                            <div class="input-group">
                                <input type="number" name="max_discount" 
                                       class="form-control form-control-modern @error('max_discount') is-invalid @enderror" 
                                       value="{{ old('max_discount') }}" 
                                       min="0" step="1000" placeholder="Không giới hạn">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            <small class="text-muted">Chỉ áp dụng cho giảm theo %</small>
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
                                   value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" 
                                   class="form-control form-control-modern @error('end_date') is-invalid @enderror" 
                                   value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
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
                                   value="{{ old('usage_limit') }}" 
                                   min="1" placeholder="Không giới hạn">
                            <small class="text-muted">Để trống nếu không giới hạn</small>
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="isActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Kích hoạt ngay
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Level tối thiểu</label>
                            <select name="min_level_required" class="form-control form-control-modern @error('min_level_required') is-invalid @enderror">
                                <option value="0" {{ old('min_level_required', 0) == 0 ? 'selected' : '' }}>Không yêu cầu (tất cả)</option>
                                <option value="2" {{ old('min_level_required') == 2 ? 'selected' : '' }}>Lv.2 🥾 Khám phá trở lên</option>
                                <option value="3" {{ old('min_level_required') == 3 ? 'selected' : '' }}>Lv.3 ⛰️ Nhà leo núi trở lên</option>
                                <option value="4" {{ old('min_level_required') == 4 ? 'selected' : '' }}>Lv.4 🏔️ Chinh phục gia trở lên</option>
                                <option value="5" {{ old('min_level_required') == 5 ? 'selected' : '' }}>Lv.5 🦅 Dũng sĩ trở lên</option>
                                <option value="6" {{ old('min_level_required') == 6 ? 'selected' : '' }}>Lv.6 👑 Huyền thoại</option>
                            </select>
                            <small class="text-muted">Chỉ user đạt level này mới dùng được mã</small>
                            @error('min_level_required')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Tạo mã giảm giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-lightbulb me-2"></i>Hướng dẫn</h5>
            </div>
            <div class="card-body-modern">
                <div class="mb-3">
                    <h6><i class="bi bi-percent text-primary me-2"></i>Giảm theo phần trăm</h6>
                    <p class="small text-muted mb-0">
                        Giảm theo % của tổng đơn hàng. Có thể đặt giới hạn giảm tối đa để tránh giảm quá nhiều cho đơn hàng lớn.
                    </p>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-cash text-success me-2"></i>Giảm số tiền cố định</h6>
                    <p class="small text-muted mb-0">
                        Giảm một số tiền cố định bất kể giá trị đơn hàng. Phù hợp cho các chương trình khuyến mãi đơn giản.
                    </p>
                </div>
                <div>
                    <h6><i class="bi bi-info-circle text-info me-2"></i>Lưu ý</h6>
                    <ul class="small text-muted mb-0">
                        <li>Mã giảm giá phải là duy nhất</li>
                        <li>Mỗi booking chỉ dùng được 1 mã</li>
                        <li>Có thể tắt/bật mã bất cứ lúc nào</li>
                    </ul>
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
    const generateBtn = document.getElementById('generateCode');
    const codeInput = document.querySelector('input[name="code"]');

    // Toggle max discount visibility based on type
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

    // Generate random code
    generateBtn.addEventListener('click', function() {
        fetch('{{ route("admin.coupons.generateCode") }}')
            .then(response => response.json())
            .then(data => {
                codeInput.value = data.code;
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    // Auto uppercase code input
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
@endsection
