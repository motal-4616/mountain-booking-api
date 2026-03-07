<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Coupon;
use App\Http\Resources\BookingResource;
use App\Services\NotificationService;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiBookingController extends ApiController
{
    protected $notificationService;
    protected $vnPayService;

    public function __construct(NotificationService $notificationService, VNPayService $vnPayService)
    {
        $this->notificationService = $notificationService;
        $this->vnPayService = $vnPayService;
    }

    /**
     * Get user's bookings
     */
    public function index(Request $request)
    {
        $query = Auth::user()->bookings()
            ->with(['schedule.tour', 'payments', 'coupon'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            // This requires custom filtering since payment_status is computed
            $bookings = $query->get()->filter(function($booking) use ($request) {
                return $booking->payment_status === $request->payment_status;
            });
            
            $perPage = $request->get('per_page', 15);
            $currentPage = $request->get('page', 1);
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $bookings->forPage($currentPage, $perPage),
                $bookings->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url()]
            );

            return $this->successResponseWithMeta(
                BookingResource::collection($paginated),
                $this->getPaginationMeta($paginated),
                'Lấy danh sách bookings thành công'
            );
        }

        $perPage = $request->get('per_page', 15);
        $bookings = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            BookingResource::collection($bookings),
            $this->getPaginationMeta($bookings),
            'Lấy danh sách bookings thành công'
        );
    }

    /**
     * Get booking details
     */
    public function show(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền xem booking này');
        }

        $booking->load(['schedule.tour', 'payments', 'coupon', 'user']);

        return $this->successResponse(
            new BookingResource($booking),
            'Lấy chi tiết booking thành công'
        );
    }

    /**
     * Create new booking
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'contact_email' => ['required', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
        ], [
            'schedule_id.required' => 'Vui lòng chọn lịch khởi hành',
            'schedule_id.exists' => 'Lịch khởi hành không hợp lệ',
            'quantity.required' => 'Vui lòng nhập số lượng vé',
            'quantity.min' => 'Số lượng vé tối thiểu là 1',
            'contact_name.required' => 'Vui lòng nhập tên liên hệ',
            'contact_phone.required' => 'Vui lòng nhập số điện thoại',
            'contact_email.required' => 'Vui lòng nhập email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            // Get schedule with pessimistic lock to prevent race conditions
            $schedule = Schedule::with('tour')->lockForUpdate()->findOrFail($request->schedule_id);

            // Validate schedule
            if (!$schedule->is_active) {
                return $this->errorResponse('Lịch khởi hành không còn hoạt động', null, 'SCHEDULE_INACTIVE', 400);
            }

            if (!$schedule->can_register) {
                return $this->errorResponse('Đã hết hạn đăng ký cho lịch này', null, 'REGISTRATION_CLOSED', 400);
            }

            if ($schedule->available_slots < $request->quantity) {
                return $this->errorResponse(
                    'Không đủ chỗ trống. Chỉ còn ' . $schedule->available_slots . ' chỗ',
                    null,
                    'INSUFFICIENT_SLOTS',
                    400
                );
            }

            // Calculate price
            $totalAmount = $schedule->price * $request->quantity;
            $discountAmount = 0;
            $levelDiscountAmount = 0;
            $couponId = null;

            // Apply level discount first
            $user = Auth::user();
            $levelDiscount = $user->level_discount ?? 0;
            if ($levelDiscount > 0) {
                $levelDiscountAmount = round($totalAmount * ($levelDiscount / 100), 0);
            }

            // Apply coupon if provided (on amount after level discount)
            $amountAfterLevel = $totalAmount - $levelDiscountAmount;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();
                
                if ($coupon && $coupon->is_valid) {
                    // Check min_level_required for exclusive coupons
                    if ($coupon->min_level_required > 0 && ($user->current_level ?? 1) < $coupon->min_level_required) {
                        return $this->errorResponse(
                            'Mã giảm giá này yêu cầu Level ' . $coupon->min_level_required . ' trở lên',
                            null, 'COUPON_LEVEL_REQUIRED', 400
                        );
                    }
                    if ($amountAfterLevel >= $coupon->min_order_amount) {
                        $discountAmount = $coupon->calculateDiscount($amountAfterLevel);
                        $couponId = $coupon->id;
                    }
                }
            }

            $finalPrice = $totalAmount - $levelDiscountAmount - $discountAmount;

            // Create booking
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'schedule_id' => $schedule->id,
                'coupon_id' => $couponId,
                'quantity' => $request->quantity,
                'contact_name' => $request->contact_name,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'note' => $request->note,
                'total_amount' => $totalAmount,
                'level_discount_amount' => $levelDiscountAmount,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice,
                'status' => 'pending',
            ]);

            // Update schedule slots
            $schedule->decrement('available_slots', $request->quantity);

            // Update coupon usage if applied
            if ($couponId) {
                $coupon->increment('used_count');
            }

            // Send notifications
            try {
                $this->notificationService->notifyNewBooking($booking);
                if ($booking->coupon_id) {
                    $this->notificationService->notifyBookingWithCoupon($booking);
                }
            } catch (\Exception $e) {
                // Log error but don't fail booking
                Log::error('Failed to send booking notification: ' . $e->getMessage());
            }

            DB::commit();

            $booking->load(['schedule.tour', 'coupon']);

            return $this->successResponse(
                new BookingResource($booking),
                'Đặt vé thành công',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Có lỗi xảy ra khi đặt vé: ' . $e->getMessage());
        }
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền hủy booking này');
        }

        // Check if can cancel
        if (!$booking->can_cancel) {
            return $this->errorResponse(
                'Không thể hủy booking này. Chỉ có thể hủy booking có trạng thái pending hoặc confirmed',
                null,
                'CANNOT_CANCEL',
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            // Kiểm tra đã thanh toán qua VNPay chưa
            $paidAmount = $booking->paid_amount;
            $vnpayPayment = $booking->payments()
                ->where('payment_method', 'vnpay')
                ->where('status', 'success')
                ->first();

            if ($paidAmount > 0 && $vnpayPayment) {
                // Có thanh toán VNPay -> chuyển sang trạng thái đang xử lý hoàn tiền
                $booking->update([
                    'status' => 'refund_processing',
                    'cancellation_reason' => $request->reason,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'refund_status' => 'processing',
                ]);

                // Return slots to schedule
                $booking->schedule->increment('available_slots', $booking->quantity);

                // Return coupon usage if applied
                if ($booking->coupon_id) {
                    $booking->coupon->decrement('used_count');
                }

                // Gửi thông báo đang xử lý hoàn tiền
                try {
                    $booking->load(['schedule.tour']);
                    $this->notificationService->notifyBookingCancelled($booking, Auth::user(), false);
                    $this->notificationService->notifyRefundProcessing($booking);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification: ' . $e->getMessage());
                }

                DB::commit();

                // Gọi VNPay Refund API (sau khi commit để không block transaction)
                $this->processVnpayRefund($booking, $paidAmount, $vnpayPayment);

                $booking->refresh();
                $booking->load(['schedule.tour', 'payments', 'coupon']);

                return $this->successResponse(
                    new BookingResource($booking),
                    'Đơn đã hủy, đang xử lý hoàn tiền'
                );
            } else {
                // Không có thanh toán VNPay hoặc chưa thanh toán -> hủy thẳng
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->reason,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                ]);

                // Return slots to schedule
                $booking->schedule->increment('available_slots', $booking->quantity);

                // Return coupon usage if applied
                if ($booking->coupon_id) {
                    $booking->coupon->decrement('used_count');
                }

                // Send notification
                try {
                    $this->notificationService->notifyBookingCancelled($booking, Auth::user(), false);
                } catch (\Exception $e) {
                    Log::error('Failed to send cancellation notification: ' . $e->getMessage());
                }

                DB::commit();

                $booking->load(['schedule.tour', 'payments', 'coupon']);

                return $this->successResponse(
                    new BookingResource($booking),
                    'Hủy booking thành công'
                );
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Có lỗi xảy ra khi hủy booking: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý hoàn tiền VNPay
     */
    protected function processVnpayRefund(Booking $booking, float $amount, $vnpayPayment): void
    {
        try {
            // Lấy tất cả các payment VNPay thành công để hoàn tiền từng giao dịch
            $vnpayPayments = $booking->payments()
                ->where('payment_method', 'vnpay')
                ->where('status', 'success')
                ->get();

            $allSuccess = true;
            $lastMessage = '';
            $lastResult = null;

            foreach ($vnpayPayments as $payment) {
                // Sử dụng transaction_ref đã lưu (chính là vnp_TxnRef gốc)
                $txnRef = $payment->transaction_ref;
                
                // vnp_TransactionNo - lấy từ cột riêng hoặc fallback
                $transactionNo = $payment->vnp_transaction_no ?? '';
                
                // vnp_TransactionDate phải giống vnp_CreateDate lúc tạo URL thanh toán (theo VNPay docs)
                // vnp_TxnRef = bookingId_unixTimestamp -> lấy timestamp để tạo lại vnp_CreateDate chính xác
                $parts = explode('_', $txnRef);
                $txnTimestamp = isset($parts[1]) ? (int)$parts[1] : null;
                $transactionDate = $txnTimestamp 
                    ? date('YmdHis', $txnTimestamp) 
                    : ($payment->vnp_pay_date ?? $payment->created_at->format('YmdHis'));
                $refundAmount = (float) $payment->amount;

                $result = $this->vnPayService->refund(
                    $booking,
                    $refundAmount,
                    $transactionNo,
                    $txnRef,
                    $transactionDate,
                    Auth::user()->name ?? 'User'
                );

                $lastResult = $result;

                if ($result['success']) {
                    // Giữ nguyên payment gốc status='success' (Đã thanh toán)
                    // Tạo record hoàn tiền riêng với status='refunded' (Đã hoàn tiền)
                    $booking->payments()->create([
                        'payment_type' => $payment->payment_type,
                        'payment_method' => 'vnpay',
                        'amount' => $refundAmount,
                        'status' => 'refunded',
                        'transaction_ref' => $result['data']['vnp_TransactionNo'] ?? null,
                        'note' => 'Hoàn tiền VNPay: ' . $result['message'],
                        'confirmed_at' => now(),
                    ]);
                    $lastMessage = $result['message'];
                } else {
                    $allSuccess = false;
                    $lastMessage = $result['message'];
                    
                    $booking->payments()->create([
                        'payment_type' => $payment->payment_type,
                        'payment_method' => 'vnpay',
                        'amount' => $refundAmount,
                        'status' => 'failed',
                        'note' => 'Hoàn tiền thất bại: ' . $result['message'] . '. Cần xử lý thủ công.',
                        'confirmed_at' => now(),
                    ]);
                    break; // Dừng nếu có lỗi
                }
            }

            $booking->load(['schedule.tour']);

            if ($allSuccess) {
                // Hoàn tiền thành công
                $booking->update([
                    'status' => 'refunded',
                    'refund_status' => 'success',
                    'refund_message' => $lastMessage,
                    'refund_transaction_ref' => $lastResult['data']['vnp_TransactionNo'] ?? null,
                    'refund_processed_at' => now(),
                ]);

                // Gửi thông báo hoàn tiền thành công
                $this->notificationService->notifyRefundCompleted($booking, true, $lastMessage);
            } else {
                // Hoàn tiền thất bại
                $booking->update([
                    'status' => 'cancelled',
                    'refund_status' => 'failed',
                    'refund_message' => $lastMessage,
                    'refund_processed_at' => now(),
                ]);

                // Gửi thông báo hoàn tiền thất bại
                $this->notificationService->notifyRefundCompleted($booking, false, $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('VNPay Refund processing error: ' . $e->getMessage());

            // Nếu lỗi, chuyển về cancelled
            $booking->update([
                'status' => 'cancelled',
                'refund_status' => 'failed',
                'refund_message' => 'Lỗi xử lý: ' . $e->getMessage(),
                'refund_processed_at' => now(),
            ]);

            $this->notificationService->notifyRefundCompleted($booking, false, 'Lỗi xử lý hoàn tiền: ' . $e->getMessage());
        }
    }

    /**
     * Get booking statistics
     */
    public function statistics(Request $request)
    {
        $userId = Auth::id();

        $stats = [
            'total_bookings' => Booking::where('user_id', $userId)->count(),
            'pending_bookings' => Booking::where('user_id', $userId)->where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('user_id', $userId)->where('status', 'confirmed')->count(),
            'completed_bookings' => Booking::where('user_id', $userId)->where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::where('user_id', $userId)->where('status', 'cancelled')->count(),
            'total_spent' => Booking::where('user_id', $userId)
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('final_price'),
        ];

        return $this->successResponse($stats, 'Lấy thống kê booking thành công');
    }

    /**
     * Download booking ticket as PDF
     */
    public function downloadTicket(Booking $booking)
    {
        // Check ownership
        if ($booking->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền truy cập booking này');
        }

        // Only allow for confirmed or completed bookings
        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            return $this->errorResponse('Chỉ có thể tải vé cho booking đã xác nhận hoặc hoàn thành', 400);
        }

        $booking->load(['schedule.tour', 'user', 'coupon', 'payments']);

        // Generate QR code as data URL using simple-qrcode or inline SVG
        $qrCodeDataUrl = $this->generateQrDataUrl($booking);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.booking-ticket', [
            'booking' => $booking,
            'qrCodeDataUrl' => $qrCodeDataUrl,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = "ve-{$booking->booking_code}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Generate a QR code as SVG data URL for the booking (no external API needed)
     */
    private function generateQrDataUrl(Booking $booking): string
    {
        $content = "MOUNTAINBOOKING:{$booking->booking_code}:{$booking->id}";

        try {
            $svg = $this->generateQrSvg($content, 150);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("QR generation failed for booking {$booking->id}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate QR code as SVG string using bit matrix encoding
     */
    private function generateQrSvg(string $data, int $size = 150): string
    {
        $matrix = $this->encodeQr($data);
        $moduleCount = count($matrix);
        $moduleSize = $size / $moduleCount;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';

        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if ($matrix[$row][$col]) {
                    $x = round($col * $moduleSize, 2);
                    $y = round($row * $moduleSize, 2);
                    $w = round($moduleSize, 2);
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $w . '" fill="#1a1a2e"/>';
                }
            }
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Encode data into QR code bit matrix (Version 2, Error Correction L, Byte mode)
     */
    private function encodeQr(string $data): array
    {
        // QR Version 2: 25×25 modules, EC Level L, max 32 bytes
        $version = 2;
        $moduleCount = 17 + $version * 4; // 25

        // Initialize matrix: null = not yet placed
        $matrix = array_fill(0, $moduleCount, array_fill(0, $moduleCount, null));
        $reserved = array_fill(0, $moduleCount, array_fill(0, $moduleCount, false));

        // Place finder patterns (3 corners)
        $this->placeFinderPattern($matrix, $reserved, 0, 0);
        $this->placeFinderPattern($matrix, $reserved, $moduleCount - 7, 0);
        $this->placeFinderPattern($matrix, $reserved, 0, $moduleCount - 7);

        // Place alignment pattern for version 2 at (18, 18)
        $this->placeAlignmentPattern($matrix, $reserved, 18, 18);

        // Place timing patterns
        for ($i = 8; $i < $moduleCount - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            $matrix[6][$i] = $val; $reserved[6][$i] = true;
            $matrix[$i][6] = $val; $reserved[$i][6] = true;
        }

        // Reserve format info areas
        for ($i = 0; $i < 8; $i++) {
            $reserved[$i][8] = true;
            $reserved[8][$i] = true;
            $reserved[$moduleCount - 1 - $i][8] = true;
            $reserved[8][$moduleCount - 1 - $i] = true;
        }
        $reserved[8][8] = true;
        $matrix[$moduleCount - 8][8] = 1; $reserved[$moduleCount - 8][8] = true; // dark module

        // Encode data: byte mode indicator (0100), character count (8 bits), data bytes
        $bits = '0100'; // Byte mode
        $bits .= str_pad(decbin(strlen($data)), 8, '0', STR_PAD_LEFT);
        for ($i = 0; $i < strlen($data); $i++) {
            $bits .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }
        $bits .= '0000'; // Terminator

        // Pad to required codewords (Version 2-L: 34 total data+EC codewords, 28 data)
        $dataCodewords = 28;
        while (strlen($bits) < $dataCodewords * 8) {
            $bits .= (strlen($bits) / 8) % 2 === 0 ? '11101100' : '00010001';
        }
        $bits = substr($bits, 0, $dataCodewords * 8);

        // Generate EC codewords using Reed-Solomon (simplified: 10 EC codewords for V2-L)
        $codewords = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $codewords[] = bindec(substr($bits, $i, 8));
        }
        $ecCodewords = $this->generateECCodewords($codewords, 10);

        // Combine data + EC into final bit string
        $allBits = '';
        foreach (array_merge($codewords, $ecCodewords) as $cw) {
            $allBits .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
        }

        // Place data bits in matrix (upward/downward columns, right to left, skipping col 6)
        $bitIndex = 0;
        $up = true;
        for ($col = $moduleCount - 1; $col >= 0; $col -= 2) {
            if ($col === 6) $col = 5; // Skip timing column
            $rows = $up ? range($moduleCount - 1, 0, -1) : range(0, $moduleCount - 1);
            foreach ($rows as $row) {
                for ($c = 0; $c < 2; $c++) {
                    $curCol = $col - $c;
                    if ($curCol < 0) continue;
                    if (!$reserved[$row][$curCol]) {
                        $matrix[$row][$curCol] = ($bitIndex < strlen($allBits)) ? (int)$allBits[$bitIndex++] : 0;
                    }
                }
            }
            $up = !$up;
        }

        // Apply mask pattern 0: (row + col) % 2 === 0
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if (!$reserved[$row][$col] && ($row + $col) % 2 === 0) {
                    $matrix[$row][$col] ^= 1;
                }
            }
        }

        // Place format info (EC Level L = 01, Mask 0 = 000 → 01000, with BCH)
        $formatBits = '111011111000100'; // Pre-computed for L + mask 0
        $formatPositions1 = [[0,8],[1,8],[2,8],[3,8],[4,8],[5,8],[7,8],[8,8],[8,7],[8,5],[8,4],[8,3],[8,2],[8,1],[8,0]];
        $formatPositions2 = [[8,$moduleCount-1],[8,$moduleCount-2],[8,$moduleCount-3],[8,$moduleCount-4],[8,$moduleCount-5],[8,$moduleCount-6],[8,$moduleCount-7],[$moduleCount-7,8],[$moduleCount-6,8],[$moduleCount-5,8],[$moduleCount-4,8],[$moduleCount-3,8],[$moduleCount-2,8],[$moduleCount-1,8]];

        for ($i = 0; $i < 15; $i++) {
            $val = (int)$formatBits[$i];
            [$r1, $c1] = $formatPositions1[$i];
            $matrix[$r1][$c1] = $val;
            if ($i < 14) {
                [$r2, $c2] = $formatPositions2[$i];
                $matrix[$r2][$c2] = $val;
            }
        }

        // Fill any remaining nulls with 0
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if ($matrix[$row][$col] === null) $matrix[$row][$col] = 0;
            }
        }

        return $matrix;
    }

    private function placeFinderPattern(array &$matrix, array &$reserved, int $row, int $col): void
    {
        $pattern = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1],
        ];
        $size = count($matrix);
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $mr = $row + $r; $mc = $col + $c;
                if ($mr < 0 || $mr >= $size || $mc < 0 || $mc >= $size) continue;
                if ($r >= 0 && $r < 7 && $c >= 0 && $c < 7) {
                    $matrix[$mr][$mc] = $pattern[$r][$c];
                } else {
                    $matrix[$mr][$mc] = 0; // Separator
                }
                $reserved[$mr][$mc] = true;
            }
        }
    }

    private function placeAlignmentPattern(array &$matrix, array &$reserved, int $centerRow, int $centerCol): void
    {
        $pattern = [[1,1,1,1,1],[1,0,0,0,1],[1,0,1,0,1],[1,0,0,0,1],[1,1,1,1,1]];
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $matrix[$centerRow + $r][$centerCol + $c] = $pattern[$r + 2][$c + 2];
                $reserved[$centerRow + $r][$centerCol + $c] = true;
            }
        }
    }

    /**
     * Generate Reed-Solomon error correction codewords (GF(256))
     */
    private function generateECCodewords(array $data, int $ecCount): array
    {
        // Generator polynomial coefficients for ecCount=10
        $gp = [0, 251, 67, 46, 61, 118, 70, 64, 94, 32, 45];

        // GF(256) tables
        $expTable = []; $logTable = [];
        $x = 1;
        for ($i = 0; $i < 256; $i++) {
            $expTable[$i] = $x;
            $logTable[$x] = $i;
            $x <<= 1;
            if ($x >= 256) $x ^= 0x11d;
        }

        $result = array_fill(0, $ecCount, 0);
        foreach ($data as $byte) {
            $coef = $byte ^ $result[0];
            array_shift($result);
            $result[] = 0;
            if ($coef === 0) continue;
            $logCoef = $logTable[$coef];
            for ($i = 0; $i < $ecCount; $i++) {
                $result[$i] ^= $expTable[($logCoef + $gp[$i + 1]) % 255];
            }
        }
        return $result;
    }
}
