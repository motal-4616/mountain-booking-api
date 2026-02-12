@extends('admin.layouts.app')

@section('title', 'Báo cáo Doanh thu')
@section('page-title', 'Báo cáo Doanh thu')

@section('content')
<!-- Thống kê tổng quan -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($totalStats['total_bookings']) }}</div>
            <div class="stat-card-label">Tổng đơn đặt</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($totalStats['total_revenue'] / 1000000, 1) }}M</div>
            <div class="stat-card-label">Doanh thu đã thu (VNĐ)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($totalStats['pending_revenue'] / 1000000, 1) }}M</div>
            <div class="stat-card-label">Còn phải thu (VNĐ)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ number_format($totalStats['total_amount'] / 1000000, 1) }}M</div>
            <div class="stat-card-label">Tổng giá trị (VNĐ)</div>
        </div>
    </div>
</div>

<!-- Bộ lọc báo cáo -->
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <form method="GET" action="{{ route('admin.revenue.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-list-ul me-1"></i>Loại báo cáo
                </label>
                <select name="type" class="form-select form-control-modern" id="reportType">
                    <option value="day" {{ $reportType == 'day' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="month" {{ $reportType == 'month' ? 'selected' : '' }}>Theo tháng</option>
                    <option value="year" {{ $reportType == 'year' ? 'selected' : '' }}>Theo năm</option>
                    <option value="custom" {{ $reportType == 'custom' ? 'selected' : '' }}>Tùy chỉnh</option>
                </select>
            </div>

            <div class="col-md-2" id="yearFilter">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar me-1"></i>Năm
                </label>
                <select name="year" class="form-select form-control-modern">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2" id="monthFilter" style="{{ $reportType == 'day' ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-month me-1"></i>Tháng
                </label>
                <select name="month" class="form-select form-control-modern">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2" id="fromDateFilter" style="{{ $reportType == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i>Từ ngày
                </label>
                <input type="date" name="from_date" class="form-control form-control-modern" value="{{ $fromDate }}">
            </div>

            <div class="col-md-2" id="toDateFilter" style="{{ $reportType == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i>Đến ngày
                </label>
                <input type="date" name="to_date" class="form-control form-control-modern" value="{{ $toDate }}">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                    <i class="bi bi-search me-1"></i>Xem báo cáo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Biểu đồ và Bảng chi tiết -->
<div class="row g-4">
    <div class="col-md-8">
        <!-- Biểu đồ doanh thu -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart"></i> Biểu đồ Doanh thu</h5>
            </div>
            <div class="card-body-modern">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>

        <!-- Bảng chi tiết doanh thu -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-table"></i> Chi tiết Doanh thu</h5>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>
                                    @if($reportType == 'day' || $reportType == 'custom')
                                        Ngày
                                    @elseif($reportType == 'month')
                                        Tháng
                                    @else
                                        Năm
                                    @endif
                                </th>
                                <th class="text-end">Số đơn</th>
                                <th class="text-end">Doanh thu đã thu</th>
                                <th class="text-end">Tổng giá trị</th>
                                <th class="text-end">Tỷ lệ thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenueData as $item)
                                <tr>
                                    <td class="fw-semibold">
                                        @if($reportType == 'day' || $reportType == 'custom')
                                            <i class="bi bi-calendar3 text-muted me-1"></i>
                                            {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                                        @elseif($reportType == 'month')
                                            <i class="bi bi-calendar-month text-muted me-1"></i>
                                            Tháng {{ $item->month }}/{{ $year }}
                                        @else
                                            <i class="bi bi-calendar text-muted me-1"></i>
                                            Năm {{ $item->year }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge-modern badge-info">{{ number_format($item->total_bookings) }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">{{ number_format($item->revenue) }}đ</td>
                                    <td class="text-end">{{ number_format($item->total_amount) }}đ</td>
                                    <td class="text-end">
                                        @php
                                            $rate = $item->total_amount > 0 ? ($item->revenue / $item->total_amount * 100) : 0;
                                        @endphp
                                        <span class="badge-modern {{ $rate >= 80 ? 'badge-success' : ($rate >= 50 ? 'badge-warning' : 'badge-danger') }}">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Không có dữ liệu
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($revenueData->count() > 0)
                            <tfoot>
                                <tr class="fw-bold" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); border-top: 2px solid #0ea5e9;">
                                    <td style="padding: 15px;">
                                        <i class="bi bi-calculator-fill me-2 text-primary"></i>
                                        <span style="color: #0369a1; font-size: 15px;">Tổng cộng</span>
                                    </td>
                                    <td class="text-end" style="padding: 15px;">
                                        <span class="badge" style="background: #0ea5e9; color: white; padding: 6px 12px; font-size: 14px;">
                                            {{ number_format($revenueData->sum('total_bookings')) }}
                                        </span>
                                    </td>
                                    <td class="text-end" style="padding: 15px; color: #059669; font-size: 15px;">
                                        {{ number_format($revenueData->sum('revenue')) }}đ
                                    </td>
                                    <td class="text-end" style="padding: 15px; color: #0369a1; font-size: 15px;">
                                        {{ number_format($revenueData->sum('total_amount')) }}đ
                                    </td>
                                    <td class="text-end" style="padding: 15px;">
                                        @php
                                            $totalRate = $revenueData->sum('total_amount') > 0 
                                                ? ($revenueData->sum('revenue') / $revenueData->sum('total_amount') * 100) 
                                                : 0;
                                        @endphp
                                        <span class="badge" style="background: {{ $totalRate >= 80 ? '#059669' : ($totalRate >= 50 ? '#d97706' : '#dc2626') }}; color: white; padding: 6px 12px; font-size: 14px;">
                                            {{ number_format($totalRate, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Top Tour theo doanh thu -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h5><i class="bi bi-trophy"></i> Top 10 Tour</h5>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th class="text-end">Đơn</th>
                                <th class="text-end">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tourRevenue as $index => $tour)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($index < 3)
                                                <span class="badge-modern {{ $index == 0 ? 'badge-warning' : ($index == 1 ? 'badge-info' : 'badge-success') }}" style="width:28px;text-align:center;">
                                                    {{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="text-muted small fw-bold" style="width:28px;text-align:center;">{{ $index + 1 }}</span>
                                            @endif
                                            <a href="{{ route('admin.tours.edit', $tour->id) }}" 
                                               class="text-decoration-none fw-semibold"
                                               title="{{ $tour->name }}">
                                                {{ Str::limit($tour->name, 18) }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge-modern badge-info">{{ number_format($tour->total_bookings) }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success small">{{ number_format($tour->revenue / 1000) }}K</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Không có dữ liệu
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Xử lý hiển thị bộ lọc theo loại báo cáo
document.getElementById('reportType').addEventListener('change', function() {
    const type = this.value;
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const fromDateFilter = document.getElementById('fromDateFilter');
    const toDateFilter = document.getElementById('toDateFilter');
    
    // Reset all filters
    yearFilter.style.display = 'none';
    monthFilter.style.display = 'none';
    fromDateFilter.style.display = 'none';
    toDateFilter.style.display = 'none';
    
    // Show relevant filters
    if (type === 'day') {
        yearFilter.style.display = 'block';
        monthFilter.style.display = 'block';
    } else if (type === 'month') {
        yearFilter.style.display = 'block';
    } else if (type === 'custom') {
        fromDateFilter.style.display = 'block';
        toDateFilter.style.display = 'block';
    }
});

// Biểu đồ doanh thu
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($revenueData);

let labels = [];
let revenues = [];
let totalAmounts = [];

revenueData.forEach(item => {
    @if($reportType == 'day' || $reportType == 'custom')
        labels.push(new Date(item.date).toLocaleDateString('vi-VN'));
    @elseif($reportType == 'month')
        labels.push('Tháng ' + item.month);
    @else
        labels.push('Năm ' + item.year);
    @endif
    
    revenues.push(item.revenue);
    totalAmounts.push(item.total_amount);
});

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Doanh thu đã thu',
                data: revenues,
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderColor: '#10b981',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            },
            {
                label: 'Tổng giá trị',
                data: totalAmounts,
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                borderColor: '#06b6d4',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#06b6d4',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 13,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(255, 255, 255, 0.2)',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + 
                               new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection
