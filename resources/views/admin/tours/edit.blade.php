@extends('admin.layouts.app')

@section('title', 'Sửa Tour')
@section('page-title', 'Sửa Tour: ' . $tour->name)

@push('styles')
<style>
.day-badge {
    min-width: 80px;
}
.day-badge .badge {
    font-size: 13px;
    padding: 8px 12px;
}
.itinerary-item {
    border: 1px solid #e2e8f0;
    padding: 12px;
    border-radius: 8px;
    background: #f8fafc;
}
.itinerary-item:hover {
    background: #fff;
    border-color: #10b981;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('admin.tours.update', $tour) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Cột trái - Thông tin cơ bản -->
                <div class="col-lg-8">
                    <!-- Thông tin cơ bản -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-info-circle"></i> Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body-modern">
                            <!-- Tên tour -->
                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="bi bi-compass me-1 text-primary"></i>Tên tour <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-modern @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $tour->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Địa điểm -->
                                <div class="col-md-6 mb-4">
                                    <label for="location" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i>Địa điểm <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-modern @error('location') is-invalid @enderror"
                                           id="location" name="location" value="{{ old('location', $tour->location) }}" required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Độ cao -->
                                <div class="col-md-6 mb-4">
                                    <label for="altitude" class="form-label fw-semibold">
                                        <i class="bi bi-arrow-up-circle me-1 text-primary"></i>Độ cao (m)
                                    </label>
                                    <input type="number" class="form-control form-control-modern @error('altitude') is-invalid @enderror"
                                           id="altitude" name="altitude" value="{{ old('altitude', $tour->altitude) }}" min="0">
                                    @error('altitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Độ khó -->
                                <div class="col-md-4 mb-4">
                                    <label for="difficulty" class="form-label fw-semibold">
                                        <i class="bi bi-speedometer2 me-1 text-primary"></i>Độ khó <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-control-modern @error('difficulty') is-invalid @enderror" id="difficulty" name="difficulty" required>
                                        <option value="easy" {{ old('difficulty', $tour->difficulty) == 'easy' ? 'selected' : '' }}>🟢 Dễ</option>
                                        <option value="medium" {{ old('difficulty', $tour->difficulty) == 'medium' ? 'selected' : '' }}>🟡 Trung bình</option>
                                        <option value="hard" {{ old('difficulty', $tour->difficulty) == 'hard' ? 'selected' : '' }}>🔴 Khó</option>
                                    </select>
                                    @error('difficulty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Thời điểm đẹp nhất -->
                                <div class="col-md-6 mb-4">
                                    <label for="best_time" class="form-label fw-semibold">
                                        <i class="bi bi-calendar-heart me-1 text-primary"></i>Thời điểm đẹp nhất
                                    </label>
                                    <input type="text" class="form-control form-control-modern @error('best_time') is-invalid @enderror"
                                           id="best_time" name="best_time" value="{{ old('best_time', $tour->best_time) }}">
                                    @error('best_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Mô tả ngắn -->
                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold">
                                    <i class="bi bi-card-text me-1 text-primary"></i>Mô tả ngắn
                                </label>
                                <textarea class="form-control form-control-modern @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description', $tour->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tổng quan -->
                            <div class="mb-4">
                                <label for="overview" class="form-label fw-semibold">
                                    <i class="bi bi-file-text me-1 text-primary"></i>Tổng quan chi tiết
                                </label>
                                <textarea class="form-control form-control-modern @error('overview') is-invalid @enderror"
                                          id="overview" name="overview" rows="5">{{ old('overview', $tour->overview) }}</textarea>
                                @error('overview')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Lịch trình -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-calendar-week"></i> Lịch trình chi tiết</h5>
                            <button type="button" class="btn btn-sm btn-primary-modern" onclick="addItineraryItem()">
                                <i class="bi bi-plus"></i> Thêm ngày
                            </button>
                        </div>
                        <div class="card-body-modern">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>Hướng dẫn:</strong> Nhập lịch trình cho tour <strong>chuẩn ({{ $tour->duration_days }} ngày)</strong>. 
                                Hệ thống sẽ tự động điều chỉnh hiển thị cho các chuyến đi có thời gian khác nhau (1 ngày, kéo dài, v.v.)
                            </div>
                            <div id="itinerary-container">
                                @php
                                    $itineraryData = $tour->itinerary ?? [];
                                    if (!is_array($itineraryData)) {
                                        $itineraryData = [];
                                    }
                                @endphp
                                @if(count($itineraryData) > 0)
                                    @foreach($itineraryData as $index => $dayContent)
                                        <div class="itinerary-item mb-3" data-index="{{ $index }}">
                                            <div class="d-flex align-items-start gap-2">
                                                <div class="day-badge">
                                                    <span class="badge bg-primary">Ngày {{ $index + 1 }}</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <textarea class="form-control form-control-modern" name="itinerary[]" rows="3" 
                                                              placeholder="Mô tả hoạt động trong ngày {{ $index + 1 }}...">{{ is_string($dayContent) ? $dayContent : '' }}</textarea>
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItineraryItem(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="itinerary-item mb-3" data-index="0">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="day-badge">
                                                <span class="badge bg-primary">Ngày 1</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <textarea class="form-control form-control-modern" name="itinerary[]" rows="3"
                                                          placeholder="Mô tả hoạt động trong ngày 1..."></textarea>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItineraryItem(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-lightbulb"></i> <strong>Gợi ý:</strong> 
                                Nhập lịch trình chuẩn của tour. Ví dụ: "Khởi hành lúc 6h sáng, trekking đến trạm dừng chân đầu tiên, ăn trưa, tiếp tục leo núi, dựng trại tại độ cao 1500m"
                            </small>
                        </div>
                    </div>

                    <!-- Điểm nổi bật -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-star"></i> Điểm nổi bật</h5>
                        </div>
                        <div class="card-body-modern">
                            <textarea class="form-control form-control-modern @error('highlights') is-invalid @enderror"
                                      id="highlights" name="highlights" rows="4">{{ old('highlights', $tour->highlights) }}</textarea>
                            <small class="text-muted"><i class="bi bi-info-circle"></i> Mỗi điểm nổi bật trên một dòng</small>
                            @error('highlights')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Bao gồm / Không bao gồm -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-modern mb-4">
                                <div class="card-header-modern bg-success text-white">
                                    <h5 class="text-white mb-0"><i class="bi bi-check-circle"></i> Dịch vụ bao gồm</h5>
                                </div>
                                <div class="card-body-modern">
                                    <textarea class="form-control form-control-modern @error('includes') is-invalid @enderror"
                                              id="includes" name="includes" rows="5">{{ old('includes', $tour->includes) }}</textarea>
                                    @error('includes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-modern mb-4">
                                <div class="card-header-modern bg-danger text-white">
                                    <h5 class="text-white mb-0"><i class="bi bi-x-circle"></i> Không bao gồm</h5>
                                </div>
                                <div class="card-body-modern">
                                    <textarea class="form-control form-control-modern @error('excludes') is-invalid @enderror"
                                              id="excludes" name="excludes" rows="5">{{ old('excludes', $tour->excludes) }}</textarea>
                                    @error('excludes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vị trí bản đồ -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-map"></i> Vị trí trên bản đồ</h5>
                        </div>
                        <div class="card-body-modern">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="map_lat" class="form-label fw-semibold">Vĩ độ (Latitude)</label>
                                    <input type="text" class="form-control form-control-modern @error('map_lat') is-invalid @enderror"
                                           id="map_lat" name="map_lat" value="{{ old('map_lat', $tour->map_lat) }}">
                                    @error('map_lat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="map_lng" class="form-label fw-semibold">Kinh độ (Longitude)</label>
                                    <input type="text" class="form-control form-control-modern @error('map_lng') is-invalid @enderror"
                                           id="map_lng" name="map_lng" value="{{ old('map_lng', $tour->map_lng) }}">
                                    @error('map_lng')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <small class="text-muted"><i class="bi bi-info-circle"></i> Tìm tọa độ trên Google Maps</small>
                        </div>
                    </div>
                </div>

                <!-- Cột phải - Ảnh và trạng thái -->
                <div class="col-lg-4">
                    <!-- Ảnh đại diện -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-image"></i> Ảnh đại diện</h5>
                        </div>
                        <div class="card-body-modern">
                            @if($tour->image)
                                <div class="mb-3 text-center">
                                    <img src="{{ asset($tour->image) }}" class="img-fluid rounded shadow-sm" style="max-height: 200px;" alt="{{ $tour->name }}">
                                </div>
                            @endif
                            <input type="file" class="form-control form-control-modern @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Chọn ảnh mới để thay thế</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="image-preview" class="text-center mt-2" style="display: none;">
                                <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>

                    <!-- Gallery hiện tại -->
                    @if($tour->gallery && count($tour->gallery) > 0)
                        <div class="card-modern mb-4">
                            <div class="card-header-modern">
                                <h5><i class="bi bi-images"></i> Ảnh gallery hiện tại</h5>
                            </div>
                            <div class="card-body-modern">
                                <div class="row g-2">
                                    @foreach($tour->gallery as $index => $image)
                                        <div class="col-4">
                                            <div class="position-relative">
                                                <img src="{{ asset($image) }}" class="img-fluid rounded" alt="Gallery {{ $index + 1 }}">
                                                <div class="form-check position-absolute top-0 end-0 m-1">
                                                    <input type="checkbox" class="form-check-input" name="delete_gallery[]" 
                                                           value="{{ $image }}" id="delete_{{ $index }}">
                                                    <label class="form-check-label" for="delete_{{ $index }}">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle"></i> Đánh dấu ảnh cần xóa</small>
                            </div>
                        </div>
                    @endif

                    <!-- Thêm gallery mới -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-plus-circle"></i> Thêm ảnh gallery</h5>
                        </div>
                        <div class="card-body-modern">
                            <input type="file" class="form-control form-control-modern @error('gallery') is-invalid @enderror"
                                   id="gallery" name="gallery[]" accept="image/*" multiple onchange="previewGallery(this)">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Có thể chọn nhiều ảnh</small>
                            @error('gallery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="gallery-preview" class="row g-2 mt-2"></div>
                        </div>
                    </div>

                    <!-- Trạng thái -->
                    <div class="card-modern mb-4">
                        <div class="card-header-modern">
                            <h5><i class="bi bi-toggle-on"></i> Trạng thái</h5>
                        </div>
                        <div class="card-body-modern">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $tour->is_active) ? 'checked' : '' }} style="width: 50px; height: 26px;">
                                <label class="form-check-label fw-semibold ms-2" for="is_active">
                                    Kích hoạt tour
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Nút submit -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-modern btn-primary-modern btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật tour
                        </button>
                        <a href="{{ route('admin.tours.index') }}" class="btn btn-modern btn-secondary-modern">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itineraryIndex = {{ count($itineraryData ?? []) > 0 ? count($itineraryData) : 1 }};

function addItineraryItem() {
    const container = document.getElementById('itinerary-container');
    const newItem = document.createElement('div');
    newItem.className = 'itinerary-item mb-3';
    newItem.setAttribute('data-index', itineraryIndex);
    newItem.innerHTML = `
        <div class="d-flex align-items-start gap-2">
            <div class="day-badge">
                <span class="badge bg-primary">Ngày ${itineraryIndex + 1}</span>
            </div>
            <div class="flex-grow-1">
                <textarea class="form-control form-control-modern" name="itinerary[]" rows="3"
                          placeholder="Mô tả hoạt động trong ngày ${itineraryIndex + 1}..."></textarea>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItineraryItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    itineraryIndex++;
    
    // Update all day badges
    updateDayBadges();
}

function removeItineraryItem(btn) {
    const item = btn.closest('.itinerary-item');
    if (document.querySelectorAll('.itinerary-item').length > 1) {
        item.remove();
        updateDayBadges();
    } else {
        alert('Phải có ít nhất 1 ngày trong lịch trình');
    }
}

function updateDayBadges() {
    const items = document.querySelectorAll('.itinerary-item');
    items.forEach((item, index) => {
        const badge = item.querySelector('.day-badge .badge');
        if (badge) {
            badge.textContent = `Ngày ${index + 1}`;
        }
        const textarea = item.querySelector('textarea');
        if (textarea) {
            textarea.placeholder = `Mô tả hoạt động trong ngày ${index + 1}...`;
        }
        item.setAttribute('data-index', index);
    });
    itineraryIndex = items.length;
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGallery(input) {
    const preview = document.getElementById('gallery-preview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-4';
                col.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="Preview ${index + 1}">`;
                preview.appendChild(col);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endpush
@endsection
