<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Schedule;
use App\Models\Coupon;
use App\Http\Resources\BookingPaymentResource;
use App\Http\Resources\BookingResource;
use App\Services\VNPayService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiPaymentController extends ApiController
{
    protected $vnPayService;
    protected $notificationService;

    public function __construct(VNPayService $vnPayService, NotificationService $notificationService)
    {
        $this->vnPayService = $vnPayService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create VNPay payment URL
     */
    public function createVNPayPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['nullable', 'numeric', 'min:10000'],
            'payment_type' => ['nullable', 'in:full,deposit,remaining'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $booking = Booking::with('schedule.tour')->findOrFail($request->booking_id);

            // Check authorization
            if ($booking->user_id !== Auth::id()) {
                return $this->forbiddenResponse('Bạn không có quyền thanh toán booking này');
            }

            // Check booking status
            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                return $this->errorResponse(
                    'Không thể thanh toán cho booking này. Trạng thái không hợp lệ.',
                    null,
                    'INVALID_BOOKING_STATUS',
                    400
                );
            }

            // Calculate amount to pay
            $amountToPay = $request->amount ?? $booking->remaining_amount;

            if ($amountToPay < 10000) {
                return $this->errorResponse(
                    'Số tiền thanh toán phải ít nhất 10,000 VNĐ',
                    null,
                    'AMOUNT_TOO_LOW',
                    400
                );
            }

            if ($amountToPay > $booking->remaining_amount) {
                return $this->errorResponse(
                    'Số tiền thanh toán vượt quá số tiền còn lại',
                    null,
                    'AMOUNT_EXCEEDED',
                    400
                );
            }

            // Determine payment type
            $paymentType = $request->payment_type ?? ($amountToPay >= $booking->final_price ? 'full' : 'deposit');

            // Cancel any existing pending VNPay payments for this booking
            // (user may have clicked pay before but didn't complete)
            BookingPayment::where('booking_id', $booking->id)
                ->where('payment_method', 'vnpay')
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                    'vnpay_response_code' => 'CANCELLED_BY_NEW',
                ]);

            // Create pending payment record
            $payment = BookingPayment::create([
                'booking_id' => $booking->id,
                'amount' => $amountToPay,
                'payment_method' => 'vnpay',
                'payment_type' => $paymentType,
                'status' => 'pending',
            ]);

            // Validate VNPay configuration
            if (!config('services.vnpay.tmn_code') && !env('VNPAY_TMN_CODE')) {
                Log::warning('VNPay config missing, using sandbox defaults');
            }

            // Generate VNPay URL with app return URL
            $orderInfo = "Thanh to\u00e1n booking #{$booking->id} - {$booking->schedule->tour->name}";
            $appReturnUrl = url('/payment/vnpay/app-return');
            $vnpayUrl = $this->vnPayService->createPaymentUrl($booking, $amountToPay, $orderInfo, $appReturnUrl);

            // Validate generated URL
            if (empty($vnpayUrl) || !str_starts_with($vnpayUrl, 'http')) {
                Log::error('VNPay URL generation failed', ['url' => $vnpayUrl]);
                return $this->errorResponse(
                    'Không thể tạo link thanh toán VNPay. Vui lòng thử lại.',
                    null,
                    'VNPAY_URL_ERROR',
                    500
                );
            }

            return $this->successResponse([
                'payment_id' => $payment->id,
                'payment_url' => $vnpayUrl,
                'amount' => (float) $amountToPay,
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'remaining_amount' => (float) $booking->remaining_amount,
                ],
            ], 'Tạo payment URL thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * VNPay callback handler
     */
    public function vnpayCallback(Request $request)
    {
        try {
            // Validate callback
            $isValid = $this->vnPayService->validateCallback($request);

            if (!$isValid) {
                return $this->errorResponse(
                    'Chữ ký không hợp lệ',
                    null,
                    'INVALID_SIGNATURE',
                    400
                );
            }

            $vnp_TxnRef = $request->vnp_TxnRef;
            $vnp_ResponseCode = $request->vnp_ResponseCode;
            $vnp_TransactionNo = $request->vnp_TransactionNo;
            $vnp_Amount = $request->vnp_Amount / 100;

            // Check if this is a new checkout (TxnRef starts with "C") or existing booking payment
            $isCheckout = str_starts_with($vnp_TxnRef, 'C');

            if ($isCheckout) {
                return $this->handleCheckoutCallback($vnp_TxnRef, $vnp_ResponseCode, $vnp_TransactionNo, $vnp_Amount, $request);
            }

            // Existing booking payment flow (pay remaining, etc.)
            $bookingId = explode('_', $vnp_TxnRef)[0];
            $booking = Booking::findOrFail($bookingId);

            DB::beginTransaction();
            try {
                if ($vnp_ResponseCode == '00') {
                    $payment = BookingPayment::where('booking_id', $booking->id)
                        ->where('payment_method', 'vnpay')
                        ->where('status', 'pending')
                        ->latest()
                        ->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'success',
                            'transaction_ref' => $vnp_TxnRef,
                            'vnp_transaction_no' => $vnp_TransactionNo,
                            'vnp_pay_date' => $request->vnp_PayDate ?? null,
                            'vnpay_response_code' => $vnp_ResponseCode,
                        ]);

                        try {
                            $this->notificationService->notifyPaymentSuccess($booking);
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment success notification: ' . $e->getMessage());
                        }
                    }

                    DB::commit();
                    return $this->successResponse([
                        'booking_id' => $booking->id,
                        'payment_status' => 'success',
                        'amount' => (float) $vnp_Amount,
                        'transaction_id' => $vnp_TransactionNo,
                    ], 'Thanh toán thành công');

                } else {
                    $payment = BookingPayment::where('booking_id', $booking->id)
                        ->where('payment_method', 'vnpay')
                        ->where('status', 'pending')
                        ->latest()
                        ->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'failed',
                            'vnpay_response_code' => $vnp_ResponseCode,
                        ]);
                    }

                    DB::commit();
                    return $this->errorResponse('Thanh toán thất bại', ['code' => $vnp_ResponseCode], 'PAYMENT_FAILED', 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Handle VNPay callback for new checkout flow (booking not yet created)
     */
    private function handleCheckoutCallback($vnp_TxnRef, $vnp_ResponseCode, $vnp_TransactionNo, $vnp_Amount, $request)
    {
        // Check if already processed
        $resultKey = "vnpay_result_{$vnp_TxnRef}";
        if (Cache::has($resultKey)) {
            $result = Cache::get($resultKey);
            if ($result['status'] === 'success') {
                return $this->successResponse($result, 'Thanh toán đã được xử lý');
            }
            return $this->errorResponse('Thanh toán thất bại', ['code' => $vnp_ResponseCode], 'PAYMENT_FAILED', 400);
        }

        if ($vnp_ResponseCode != '00') {
            // Payment failed - just cache the result, no booking to clean up
            Cache::put($resultKey, ['status' => 'failed', 'code' => $vnp_ResponseCode], 1800);
            Cache::forget("vnpay_checkout_{$vnp_TxnRef}");
            return $this->errorResponse('Thanh toán thất bại', ['code' => $vnp_ResponseCode], 'PAYMENT_FAILED', 400);
        }

        // Payment success - create booking now
        $bookingData = Cache::get("vnpay_checkout_{$vnp_TxnRef}");
        if (!$bookingData) {
            Log::error("VNPay checkout data not found in cache for txnRef: {$vnp_TxnRef}");
            Cache::put($resultKey, ['status' => 'failed', 'code' => 'CACHE_EXPIRED'], 1800);
            return $this->serverErrorResponse('Dữ liệu đặt tour đã hết hạn. Vui lòng đặt lại.');
        }

        DB::beginTransaction();
        try {
            $schedule = Schedule::with('tour')->lockForUpdate()->findOrFail($bookingData['schedule_id']);

            // Re-check availability
            if ($schedule->available_slots < $bookingData['quantity']) {
                DB::rollBack();
                Cache::put($resultKey, [
                    'status' => 'failed',
                    'code' => 'SOLD_OUT',
                    'message' => 'Rất tiếc, tour đã hết chỗ. Khoản thanh toán sẽ được hoàn lại.',
                ], 1800);
                Cache::forget("vnpay_checkout_{$vnp_TxnRef}");
                return $this->errorResponse('Tour đã hết chỗ', null, 'SOLD_OUT', 400);
            }

            // Create booking
            $booking = Booking::create([
                'user_id' => $bookingData['user_id'],
                'schedule_id' => $schedule->id,
                'coupon_id' => $bookingData['coupon_id'],
                'quantity' => $bookingData['quantity'],
                'contact_name' => $bookingData['contact_name'],
                'contact_phone' => $bookingData['contact_phone'],
                'contact_email' => $bookingData['contact_email'],
                'note' => $bookingData['note'],
                'total_amount' => $bookingData['total_amount'],
                'level_discount_amount' => $bookingData['level_discount_amount'],
                'discount_amount' => $bookingData['discount_amount'],
                'final_price' => $bookingData['final_price'],
                'status' => 'pending',
            ]);

            // Create successful payment record
            BookingPayment::create([
                'booking_id' => $booking->id,
                'amount' => $bookingData['amount_to_pay'],
                'payment_method' => 'vnpay',
                'payment_type' => $bookingData['payment_type'],
                'status' => 'success',
                'transaction_ref' => $vnp_TxnRef,
                'vnp_transaction_no' => $vnp_TransactionNo,
                'vnp_pay_date' => $request->vnp_PayDate ?? null,
                'vnpay_response_code' => $vnp_ResponseCode,
            ]);

            // Update schedule slots
            $schedule->decrement('available_slots', $bookingData['quantity']);

            // Update coupon usage
            if ($bookingData['coupon_id']) {
                Coupon::where('id', $bookingData['coupon_id'])->increment('used_count');
            }

            DB::commit();

            // Cache result for payment-return page to poll
            Cache::put($resultKey, [
                'status' => 'success',
                'booking_id' => $booking->id,
                'amount' => (float) $vnp_Amount,
                'transaction_id' => $vnp_TransactionNo,
            ], 1800);

            // Clean up checkout cache
            Cache::forget("vnpay_checkout_{$vnp_TxnRef}");

            // Send notifications
            try {
                $this->notificationService->notifyNewBooking($booking);
                $this->notificationService->notifyPaymentSuccess($booking);
            } catch (\Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }

            return $this->successResponse([
                'booking_id' => $booking->id,
                'payment_status' => 'success',
                'amount' => (float) $vnp_Amount,
                'transaction_id' => $vnp_TransactionNo,
            ], 'Thanh toán và đặt vé thành công');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Checkout callback error: " . $e->getMessage());
            Cache::put($resultKey, ['status' => 'failed', 'code' => 'SERVER_ERROR'], 1800);
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Create cash payment (COD)
     */
    public function createCashPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $booking = Booking::findOrFail($request->booking_id);

            // Check authorization
            if ($booking->user_id !== Auth::id()) {
                return $this->forbiddenResponse('Bạn không có quyền thao tác booking này');
            }

            // Check booking status
            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                return $this->errorResponse(
                    'Không thể tạo thanh toán cho booking này',
                    null,
                    'INVALID_BOOKING_STATUS',
                    400
                );
            }

            // Create pending cash payment
            $payment = BookingPayment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->remaining_amount,
                'payment_method' => 'cash',
                'status' => 'pending',
            ]);

            return $this->successResponse([
                'payment' => new BookingPaymentResource($payment),
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'amount' => (float) $booking->remaining_amount,
                ],
                'message' => 'Vui lòng thanh toán tiền mặt khi đến điểm khởi hành',
            ], 'Đã chọn thanh toán tiền mặt', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Check checkout result (polled by payment-return page)
     */
    public function checkoutResult(Request $request)
    {
        $ref = $request->query('ref');
        if (!$ref || !str_starts_with($ref, 'C')) {
            return $this->errorResponse('Ref không hợp lệ', null, 'INVALID_REF', 400);
        }

        $result = Cache::get("vnpay_result_{$ref}");
        if (!$result) {
            return $this->successResponse(['status' => 'processing'], 'Đang xử lý');
        }

        if ($result['status'] === 'success' && isset($result['booking_id'])) {
            $booking = Booking::with(['schedule.tour', 'payments'])->find($result['booking_id']);
            return $this->successResponse([
                'status' => 'success',
                'booking_id' => $result['booking_id'],
                'booking' => $booking ? new BookingResource($booking) : null,
                'amount' => $result['amount'] ?? 0,
                'transaction_id' => $result['transaction_id'] ?? '',
            ], 'Thanh toán thành công');
        }

        return $this->successResponse([
            'status' => 'failed',
            'code' => $result['code'] ?? 'UNKNOWN',
            'message' => $result['message'] ?? 'Thanh toán thất bại',
        ], 'Thanh toán thất bại');
    }

    /**
     * VNPay app return - processes callback and shows HTML page
     */
    public function vnpayAppReturn(Request $request)
    {
        try {
            $isValid = $this->vnPayService->validateCallback($request);
            $vnp_ResponseCode = $request->vnp_ResponseCode;
            $vnp_TxnRef = $request->vnp_TxnRef;
            $vnp_TransactionNo = $request->vnp_TransactionNo;
            $vnp_Amount = $request->vnp_Amount / 100;
            $isCheckout = str_starts_with($vnp_TxnRef, 'C');

            $success = false;
            $paymentType = '';
            $booking = null;
            $tourName = 'Tour';
            $departureDate = '';
            $quantity = 0;

            if ($isCheckout) {
                // New checkout flow
                $resultKey = "vnpay_result_{$vnp_TxnRef}";
                $cachedResult = Cache::get($resultKey);

                if ($cachedResult && $cachedResult['status'] === 'success' && isset($cachedResult['booking_id'])) {
                    // Already processed by webhook
                    $booking = Booking::with(['schedule.tour'])->find($cachedResult['booking_id']);
                    $success = true;
                } elseif ($isValid && $vnp_ResponseCode == '00') {
                    // Webhook hasn't processed yet - do it here
                    $callbackResult = $this->handleCheckoutCallback($vnp_TxnRef, $vnp_ResponseCode, $vnp_TransactionNo, $vnp_Amount, $request);
                    $cachedResult = Cache::get($resultKey);
                    if ($cachedResult && $cachedResult['status'] === 'success' && isset($cachedResult['booking_id'])) {
                        $booking = Booking::with(['schedule.tour'])->find($cachedResult['booking_id']);
                        $success = true;
                    }
                } else {
                    // Payment failed
                    $bookingData = Cache::get("vnpay_checkout_{$vnp_TxnRef}");
                    if ($bookingData) {
                        $schedule = Schedule::with('tour')->find($bookingData['schedule_id']);
                        $tourName = $schedule->tour->name ?? 'Tour';
                        $departureDate = date('d/m/Y', strtotime($schedule->departure_date));
                        $quantity = $bookingData['quantity'];
                        $paymentType = $bookingData['payment_type'] ?? '';
                        Cache::forget("vnpay_checkout_{$vnp_TxnRef}");
                    }
                    Cache::put($resultKey, ['status' => 'failed', 'code' => $vnp_ResponseCode], 1800);
                }

                if ($booking) {
                    $tourName = $booking->schedule->tour->name ?? 'Tour';
                    $departureDate = date('d/m/Y', strtotime($booking->schedule->departure_date));
                    $quantity = $booking->quantity;
                    $paymentType = $booking->payments()->where('status', 'success')->latest()->value('payment_type') ?? '';
                }
            } else {
                // Existing booking payment flow
                $bookingId = explode('_', $vnp_TxnRef)[0];
                $booking = Booking::with(['schedule.tour'])->findOrFail($bookingId);

                // Check if already processed by webhook
                $alreadyProcessed = BookingPayment::where('booking_id', $booking->id)
                    ->where('payment_method', 'vnpay')
                    ->where('transaction_ref', $vnp_TxnRef)
                    ->whereIn('status', ['success', 'failed'])
                    ->latest()->first();

                if ($alreadyProcessed) {
                    $success = $alreadyProcessed->status === 'success';
                    $paymentType = $alreadyProcessed->payment_type ?? '';
                } elseif ($isValid && $vnp_ResponseCode == '00') {
                    $payment = BookingPayment::where('booking_id', $booking->id)
                        ->where('payment_method', 'vnpay')
                        ->where('status', 'pending')
                        ->latest()->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'success',
                            'transaction_ref' => $vnp_TxnRef,
                            'vnp_transaction_no' => $vnp_TransactionNo,
                            'vnp_pay_date' => $request->vnp_PayDate ?? null,
                            'vnpay_response_code' => $vnp_ResponseCode,
                        ]);
                        $paymentType = $payment->payment_type;
                        try { $this->notificationService->notifyPaymentSuccess($booking); } catch (\Exception $e) {}
                        $success = true;
                    } else {
                        $existingSuccess = BookingPayment::where('booking_id', $booking->id)
                            ->where('payment_method', 'vnpay')
                            ->where('status', 'success')
                            ->latest()->first();
                        if ($existingSuccess) {
                            $success = true;
                            $paymentType = $existingSuccess->payment_type ?? '';
                        }
                    }
                } else {
                    $payment = BookingPayment::where('booking_id', $booking->id)
                        ->where('payment_method', 'vnpay')
                        ->where('status', 'pending')
                        ->latest()->first();
                    if ($payment) {
                        $payment->update(['status' => 'failed', 'vnpay_response_code' => $vnp_ResponseCode]);
                        $paymentType = $payment->payment_type ?? '';
                    }
                }

                $tourName = $booking->schedule->tour->name ?? 'Tour';
                $departureDate = date('d/m/Y', strtotime($booking->schedule->departure_date));
                $quantity = $booking->quantity;
            }
            $statusText = $success ? 'Thanh toán thành công!' : 'Thanh toán thất bại';
            $statusIcon = $success ? '✅' : '❌';
            $statusColor = $success ? '#29a38f' : '#e74c3c';
            $amount = number_format($vnp_Amount, 0, ',', '.');
            $paymentTypeText = $paymentType === 'deposit' ? 'Đặt cọc 30%' : 'Thanh toán đủ';

            return response("
<!DOCTYPE html>
<html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>
<title>Kết quả thanh toán</title>
<style>
* { box-sizing: border-box; }
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#161d1b;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
.container{max-width:480px;width:100%;background:#1e2826;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px rgba(0,0,0,0.4)}
.icon{font-size:80px;margin-bottom:16px;text-align:center;animation:scaleIn 0.4s ease}
@keyframes scaleIn{from{transform:scale(0.5);opacity:0}to{transform:scale(1);opacity:1}}
h1{font-size:24px;margin:0 0 8px;color:{$statusColor};text-align:center;font-weight:600}
.amt{font-size:36px;font-weight:700;color:#29a38f;margin:20px 0;text-align:center}
.code{font-size:15px;color:rgba(255,255,255,0.7);margin:16px 0;text-align:center;padding:12px;background:rgba(41,163,143,0.15);border-radius:8px;font-weight:500}
.details{margin:24px 0;padding:20px;background:rgba(255,255,255,0.05);border-radius:12px}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.1)}
.detail-row:last-child{border-bottom:none}
.detail-label{color:rgba(255,255,255,0.5);font-size:14px}
.detail-value{color:#fff;font-weight:500;font-size:14px;text-align:right}
.payment-type{display:inline-block;padding:4px 12px;background:#29a38f;border-radius:6px;font-size:12px;font-weight:500;margin-left:8px}
.hint{font-size:13px;color:rgba(255,255,255,0.4);margin-top:24px;text-align:center;line-height:1.6}
.transaction{font-size:11px;color:rgba(255,255,255,0.3);margin-top:16px;text-align:center}
</style>
</head><body><div class='container'>
<div class='icon'>{$statusIcon}</div>
<h1>{$statusText}</h1>
" . ($success ? "
<div class='amt'>{$amount} VNĐ</div>
<div class='code'>Mã booking: {$booking->booking_code}</div>
<div class='details'>
  <div class='detail-row'>
    <span class='detail-label'>Tour</span>
    <span class='detail-value'>{$tourName}</span>
  </div>
  <div class='detail-row'>
    <span class='detail-label'>Ngày khởi hành</span>
    <span class='detail-value'>{$departureDate}</span>
  </div>
  <div class='detail-row'>
    <span class='detail-label'>Số người</span>
    <span class='detail-value'>{$quantity} người</span>
  </div>
  <div class='detail-row'>
    <span class='detail-label'>Loại thanh toán</span>
    <span class='detail-value'>{$paymentTypeText}</span>
  </div>
</div>
<div class='transaction'>Mã giao dịch: {$vnp_TransactionNo}</div>
<div class='hint'>✓ Thanh toán của bạn đã được xác nhận<br>Vui lòng đóng trang này để quay lại ứng dụng và xem chi tiết đơn hàng</div>
" : "
<div class='hint'>Giao dịch không thành công<br>Vui lòng đóng trang này và thử lại</div>
") . "
</div></body></html>
            ");
        } catch (\Exception $e) {
            return response("
<!DOCTYPE html>
<html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>
<title>Lỗi</title>
<style>body{font-family:sans-serif;background:#161d1b;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center}</style>
</head><body><div><h1>❌ Có lỗi xảy ra</h1><p style='color:rgba(255,255,255,0.5)'>Vui lòng đóng trang này và quay lại ứng dụng</p></div></body></html>
            ");
        }
    }

    /**
     * Get payment history for a booking
     */
    public function getPaymentHistory(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền xem thanh toán này');
        }

        $payments = $booking->payments()->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            BookingPaymentResource::collection($payments),
            'Lấy lịch sử thanh toán thành công'
        );
    }
}
