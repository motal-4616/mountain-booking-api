<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Vé đặt tour - {{ $booking->booking_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            background: #fff;
        }
        .ticket {
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #29a38f;
            border-radius: 12px;
            overflow: hidden;
        }
        .ticket-header {
            background: linear-gradient(135deg, #29a38f, #1e8070);
            color: #fff;
            padding: 24px;
            text-align: center;
        }
        .ticket-header h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }
        .ticket-header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .booking-code-section {
            text-align: center;
            padding: 20px;
            background: #f8fffe;
            border-bottom: 1px dashed #29a38f;
        }
        .booking-code {
            font-size: 28px;
            font-weight: bold;
            color: #29a38f;
            letter-spacing: 2px;
        }
        .booking-code-label {
            font-size: 11px;
            color: #888;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .qr-section {
            text-align: center;
            padding: 16px;
            background: #f8fffe;
            border-bottom: 1px dashed #29a38f;
        }
        .qr-section img {
            width: 150px;
            height: 150px;
        }
        .qr-hint {
            font-size: 10px;
            color: #888;
            margin-top: 6px;
        }
        .info-section {
            padding: 20px 24px;
        }
        .info-section h3 {
            font-size: 14px;
            color: #29a38f;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dotted #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #888;
            font-size: 11px;
        }
        .info-value {
            font-weight: 600;
            font-size: 12px;
            text-align: right;
            max-width: 60%;
        }
        .price-value {
            color: #29a38f;
            font-size: 14px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
        }
        .status-confirmed {
            background: #29a38f;
        }
        .status-completed {
            background: #3498db;
        }
        .status-pending {
            background: #f39c12;
        }
        .ticket-footer {
            background: #f5f5f5;
            padding: 16px 24px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        .ticket-footer p {
            margin-bottom: 2px;
        }
        .divider {
            border: none;
            border-top: 1px dashed #29a38f;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Header -->
        <div class="ticket-header">
            <h1>Mountain Booking</h1>
            <p>Vé điện tử - E-Ticket</p>
        </div>

        <!-- Booking Code -->
        <div class="booking-code-section">
            <div class="booking-code-label">Mã đặt chỗ</div>
            <div class="booking-code">{{ $booking->booking_code }}</div>
        </div>

        <!-- QR Code -->
        @if($qrCodeDataUrl)
        <div class="qr-section">
            <img src="{{ $qrCodeDataUrl }}" alt="QR Code" />
            <div class="qr-hint">Quét mã QR để check-in tại điểm xuất phát</div>
        </div>
        @endif

        <!-- Tour Info -->
        <div class="info-section">
            <h3>Thông tin Tour</h3>
            <div class="info-row">
                <span class="info-label">Tên tour</span>
                <span class="info-value">{{ $booking->schedule?->tour?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày khởi hành</span>
                <span class="info-value">{{ $booking->schedule?->departure_date ? \Carbon\Carbon::parse($booking->schedule->departure_date)->format('d/m/Y') : 'N/A' }}</span>
            </div>
            @if($booking->schedule?->end_date)
            <div class="info-row">
                <span class="info-label">Ngày kết thúc</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($booking->schedule->end_date)->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Địa điểm</span>
                <span class="info-value">{{ $booking->schedule?->tour?->location ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số người</span>
                <span class="info-value">{{ $booking->quantity }} người</span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng thái</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ $booking->status === 'confirmed' ? 'Đã xác nhận' : ($booking->status === 'completed' ? 'Hoàn thành' : ucfirst($booking->status)) }}
                    </span>
                </span>
            </div>
        </div>

        <hr class="divider" />

        <!-- Contact Info -->
        <div class="info-section">
            <h3>Thông tin liên hệ</h3>
            <div class="info-row">
                <span class="info-label">Họ tên</span>
                <span class="info-value">{{ $booking->contact_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $booking->contact_email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Điện thoại</span>
                <span class="info-value">{{ $booking->contact_phone }}</span>
            </div>
            @if($booking->note)
            <div class="info-row">
                <span class="info-label">Ghi chú</span>
                <span class="info-value">{{ $booking->note }}</span>
            </div>
            @endif
        </div>

        <hr class="divider" />

        <!-- Payment Info -->
        <div class="info-section">
            <h3>Thông tin thanh toán</h3>
            <div class="info-row">
                <span class="info-label">Tổng tiền</span>
                <span class="info-value">{{ number_format($booking->total_amount, 0, ',', '.') }} ₫</span>
            </div>
            @if($booking->level_discount_amount > 0)
            <div class="info-row">
                <span class="info-label">Giảm giá Level</span>
                <span class="info-value" style="color:#27ae60">-{{ number_format($booking->level_discount_amount, 0, ',', '.') }} ₫</span>
            </div>
            @endif
            @if($booking->discount_amount > 0)
            <div class="info-row">
                <span class="info-label">Giảm giá coupon</span>
                <span class="info-value" style="color:#27ae60">-{{ number_format($booking->discount_amount, 0, ',', '.') }} ₫</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Thành tiền</span>
                <span class="info-value price-value">{{ number_format($booking->final_price, 0, ',', '.') }} ₫</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
            <p><strong>Mountain Booking</strong> - Ứng dụng đặt tour leo núi</p>
            <p>Vé được tạo lúc: {{ now()->format('d/m/Y H:i') }}</p>
            <p>Vui lòng xuất trình vé này khi check-in tại điểm xuất phát.</p>
        </div>
    </div>
</body>
</html>
