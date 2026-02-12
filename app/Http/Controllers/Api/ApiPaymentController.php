<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Http\Resources\BookingPaymentResource;
use App\Services\VNPayService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Create pending payment record
            $payment = BookingPayment::create([
                'booking_id' => $booking->id,
                'amount' => $amountToPay,
                'payment_method' => 'vnpay',
                'payment_type' => $paymentType,
                'status' => 'pending',
            ]);

            // Generate VNPay URL with app return URL
            $orderInfo = "Thanh toán booking #{$booking->id} - {$booking->schedule->tour->name}";
            $appReturnUrl = url('/payment/vnpay/app-return');
            $vnpayUrl = $this->vnPayService->createPaymentUrl($booking, $amountToPay, $orderInfo, $appReturnUrl);

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

            // Get transaction info
            $vnp_TxnRef = $request->vnp_TxnRef;
            $vnp_ResponseCode = $request->vnp_ResponseCode;
            $vnp_TransactionNo = $request->vnp_TransactionNo;
            $vnp_Amount = $request->vnp_Amount / 100;

            // Extract booking ID from TxnRef
            $bookingId = explode('_', $vnp_TxnRef)[0];
            $booking = Booking::findOrFail($bookingId);

            DB::beginTransaction();
            try {
                if ($vnp_ResponseCode == '00') {
                    // Payment success
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

                        // Don't auto-confirm - booking stays 'pending' for admin to confirm
                        // if ($booking->status === 'pending') {
                        //     $booking->update(['status' => 'confirmed']);
                        // }

                        // Send notification
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
                    // Payment failed
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

                        // Send notification
                        try {
                            $this->notificationService->notifyPaymentFailed($booking, $vnp_ResponseCode, 'Thanh toán thất bại');
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment failed notification: ' . $e->getMessage());
                        }
                    }

                    DB::commit();

                    return $this->errorResponse(
                        'Thanh toán thất bại',
                        ['code' => $vnp_ResponseCode],
                        'PAYMENT_FAILED',
                        400
                    );
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
            $bookingId = explode('_', $vnp_TxnRef)[0];
            $booking = Booking::with(['schedule.tour'])->findOrFail($bookingId);

            $success = false;
            $paymentType = '';
            if ($isValid && $vnp_ResponseCode == '00') {
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
                    // Don't auto-confirm - booking stays 'pending' for admin to confirm
                    // if ($booking->status === 'pending') {
                    //     $booking->update(['status' => 'confirmed']);
                    // }
                    try { $this->notificationService->notifyPaymentSuccess($booking); } catch (\Exception $e) {}
                    $success = true;
                }
            } else {
                $payment = BookingPayment::where('booking_id', $booking->id)
                    ->where('payment_method', 'vnpay')
                    ->where('status', 'pending')
                    ->latest()->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'failed',
                        'vnpay_response_code' => $vnp_ResponseCode,
                    ]);
                    $paymentType = $payment->payment_type ?? '';
                }
            }

            $tourName = $booking->schedule->tour->name ?? 'Tour';
            $departureDate = date('d/m/Y', strtotime($booking->schedule->departure_date));
            $quantity = $booking->quantity;
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
