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
            // Get schedule
            $schedule = Schedule::with('tour')->findOrFail($request->schedule_id);

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
            $couponId = null;

            // Apply coupon if provided
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();
                
                if ($coupon && $coupon->is_valid) {
                    if ($totalAmount >= $coupon->min_order_amount) {
                        $discountAmount = $coupon->calculateDiscount($totalAmount);
                        $couponId = $coupon->id;
                    }
                }
            }

            $finalPrice = $totalAmount - $discountAmount;

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
}
