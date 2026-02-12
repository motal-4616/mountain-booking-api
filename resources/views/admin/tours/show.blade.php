@extends('admin.layouts.app')

@section('title', 'Chi tiết Tour')

@section('content')
<div class="admin-content">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="fw-bold fs-3 mb-0">
                <i class="bi bi-eye text-primary"></i> Chi tiết Tour
            </h1>
            <div>
                <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-primary-modern me-2">
                    <i class="bi bi-pencil"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cột trái - Thông tin chính -->
        <div class="col-lg-8">
            <!-- Thông tin cơ bản -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-info-circle"></i> Thông tin cơ bản</h5>
                </div>
                <div class="card-body-modern">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tên tour</label>
                            <p class="fw-bold">{{ $tour->name }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Độ khó</label>
                            <p>
                                @if($tour->difficulty === 'easy')
                                    <span class="badge bg-success">Dễ</span>
                                @elseif($tour->difficulty === 'medium')
                                    <span class="badge bg-warning">Trung bình</span>
                                @else
                                    <span class="badge bg-danger">Khó</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Trạng thái</label>
                            <p>
                                @if($tour->is_active)
                                    <span class="badge bg-success">Đang hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Tạm dừng</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Địa điểm</label>
                            <p><i class="bi bi-geo-alt text-primary"></i> {{ $tour->location }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Giá cơ bản (từ schedules)</label>
                            @php
                                $schedulePrices = $tour->schedules()->where('price', '>', 0)->pluck('price');
                                $minPrice = $schedulePrices->min();
                                $maxPrice = $schedulePrices->max();
                            @endphp
                            @if($minPrice && $maxPrice)
                                @if($minPrice == $maxPrice)
                                    <p class="fw-bold text-primary fs-5">{{ number_format($minPrice) }}đ</p>
                                @else
                                    <p class="fw-bold text-primary fs-5">{{ number_format($minPrice) }}đ - {{ number_format($maxPrice) }}đ</p>
                                @endif
                            @else
                                <p class="text-muted">Chưa thiết lập giá</p>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Thời gian chuẩn</label>
                            <p><i class="bi bi-calendar-week"></i> {{ $tour->duration_days }} ngày</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">Độ cao</label>
                            <p><i class="bi bi-activity"></i> {{ $tour->altitude ? number_format($tour->altitude) . 'm' : 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Thời gian tốt nhất</label>
                            <p>{{ $tour->best_time ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tọa độ bản đồ</label>
                            <p>
                                @if($tour->map_lat && $tour->map_lng)
                                    <i class="bi bi-pin-map"></i> {{ $tour->map_lat }}, {{ $tour->map_lng }}
                                    <a href="https://www.google.com/maps?q={{ $tour->map_lat }},{{ $tour->map_lng }}" target="_blank" class="ms-2">
                                        <i class="bi bi-box-arrow-up-right"></i> Xem bản đồ
                                    </a>
                                @else
                                    Chưa cập nhật
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mô tả tổng quan -->
            @if($tour->overview)
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-file-text"></i> Tổng quan</h5>
                </div>
                <div class="card-body-modern">
                    <p class="mb-0" style="white-space: pre-line;">{{ $tour->overview }}</p>
                </div>
            </div>
            @endif

            <!-- Mô tả chi tiết -->
            @if($tour->description)
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-text-paragraph"></i> Mô tả chi tiết</h5>
                </div>
                <div class="card-body-modern">
                    <p class="mb-0" style="white-space: pre-line;">{{ $tour->description }}</p>
                </div>
            </div>
            @endif

            <!-- Lịch trình -->
            @if($tour->itinerary && is_array($tour->itinerary) && count($tour->itinerary) > 0)
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-calendar-week"></i> Lịch trình chi tiết ({{ count($tour->itinerary) }} ngày)</h5>
                </div>
                <div class="card-body-modern">
                    <div class="timeline-admin">
                        @foreach($tour->itinerary as $index => $day)
                        <div class="timeline-item-admin">
                            <div class="timeline-marker-admin">{{ $index + 1 }}</div>
                            <div class="timeline-content-admin">
                                <h6>Ngày {{ $index + 1 }}</h6>
                                <p class="mb-0">{{ $day }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Bao gồm / Không bao gồm -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card-modern h-100">
                        <div class="card-header-modern bg-success-subtle">
                            <h5><i class="bi bi-check-circle text-success"></i> Bao gồm</h5>
                        </div>
                        <div class="card-body-modern">
                            @if($tour->includes_list && count($tour->includes_list) > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($tour->includes_list as $item)
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>{{ $item }}
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Chưa cập nhật</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-modern h-100">
                        <div class="card-header-modern bg-danger-subtle">
                            <h5><i class="bi bi-x-circle text-danger"></i> Không bao gồm</h5>
                        </div>
                        <div class="card-body-modern">
                            @if($tour->excludes_list && count($tour->excludes_list) > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($tour->excludes_list as $item)
                                    <li class="mb-2">
                                        <i class="bi bi-x-circle-fill text-danger me-2"></i>{{ $item }}
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">Chưa cập nhật</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Điểm nổi bật -->
            @if($tour->highlights_list && count($tour->highlights_list) > 0)
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-star"></i> Điểm nổi bật</h5>
                </div>
                <div class="card-body-modern">
                    <ul class="list-unstyled mb-0">
                        @foreach($tour->highlights_list as $highlight)
                        <li class="mb-2">
                            <i class="bi bi-star-fill text-warning me-2"></i>{{ $highlight }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Lịch khởi hành -->
            <div class="card-modern mb-4">
                <div class="card-header-modern d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-calendar3"></i> Lịch khởi hành ({{ $tour->schedules->count() }})</h5>
                    <a href="{{ route('admin.schedules.create', ['tour_id' => $tour->id]) }}" class="btn btn-sm btn-primary-modern">
                        <i class="bi bi-plus"></i> Thêm lịch
                    </a>
                </div>
                <div class="card-body-modern p-0">
                    @if($tour->schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày khởi hành</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Thời gian</th>
                                    <th>Số chỗ</th>
                                    <th>Đã đặt</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tour->schedules as $schedule)
                                @php
                                    $days = $schedule->end_date 
                                        ? \Carbon\Carbon::parse($schedule->departure_date)->diffInDays(\Carbon\Carbon::parse($schedule->end_date)) + 1
                                        : 1;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($schedule->departure_date)->format('d/m/Y') }}</td>
                                    <td>{{ $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') : '-' }}</td>
                                    <td><span class="badge bg-info">{{ $days }} ngày</span></td>
                                    <td>{{ $schedule->max_people }}</td>
                                    <td>
                                        <span class="badge bg-{{ $schedule->available_seats > 0 ? 'success' : 'danger' }}">
                                            {{ $schedule->current_bookings }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($schedule->price) }}đ</td>
                                    <td>
                                        @if($schedule->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-3">Chưa có lịch khởi hành nào</p>
                        <a href="{{ route('admin.schedules.create', ['tour_id' => $tour->id]) }}" class="btn btn-primary-modern">
                            <i class="bi bi-plus"></i> Thêm lịch đầu tiên
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột phải - Hình ảnh & Metadata -->
        <div class="col-lg-4">
            <!-- Ảnh chính -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-image"></i> Ảnh chính</h5>
                </div>
                <div class="card-body-modern">
                    @if($tour->image)
                        <img src="{{ asset($tour->image) }}" alt="{{ $tour->name }}" class="img-fluid rounded">
                    @else
                        <div class="text-center py-5 bg-light rounded">
                            <i class="bi bi-image text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">Chưa có ảnh</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gallery -->
            @if($tour->gallery && count($tour->gallery) > 0)
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-images"></i> Gallery ({{ count($tour->gallery) }} ảnh)</h5>
                </div>
                <div class="card-body-modern">
                    <div class="row g-2">
                        @foreach($tour->gallery as $image)
                        <div class="col-6">
                            <img src="{{ asset($image) }}" alt="Gallery" class="img-fluid rounded" style="height: 100px; object-fit: cover; width: 100%;">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Thống kê -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h5><i class="bi bi-bar-chart"></i> Thống kê</h5>
                </div>
                <div class="card-body-modern">
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Lịch khởi hành</span>
                            <span class="fw-bold">{{ $tour->schedules->count() }}</span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Lịch đang hoạt động</span>
                            <span class="fw-bold">{{ $tour->schedules->where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tổng chỗ khả dụng</span>
                            <span class="fw-bold">{{ $tour->schedules->sum('max_people') }}</span>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Đã đặt</span>
                            <span class="fw-bold text-success">{{ $tour->schedules->sum('current_bookings') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="bi bi-clock-history"></i> Metadata</h5>
                </div>
                <div class="card-body-modern">
                    <div class="mb-3">
                        <small class="text-muted">Ngày tạo</small>
                        <p class="mb-0">{{ $tour->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <small class="text-muted">Cập nhật lần cuối</small>
                        <p class="mb-0">{{ $tour->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-admin {
    position: relative;
    padding-left: 20px;
}

.timeline-item-admin {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    position: relative;
}

.timeline-item-admin:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -24px;
    width: 2px;
    background: #e2e8f0;
}

.timeline-marker-admin {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    z-index: 1;
}

.timeline-content-admin {
    flex: 1;
    padding-top: 8px;
}

.timeline-content-admin h6 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
}

.timeline-content-admin p {
    color: #64748b;
    line-height: 1.6;
}

.stats-item {
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
}

/* Schedule Table Styling */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

.table {
    margin-bottom: 0;
}

.table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table thead th {
    color: black;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    font-size: 14px;
    border-bottom: 1px solid #e2e8f0;
}

.table tbody td:first-child {
    font-weight: 600;
    color: #334155;
}

.table .badge {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
}
</style>
@endsection
