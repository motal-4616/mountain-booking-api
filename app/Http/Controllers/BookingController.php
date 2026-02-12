<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\Schedule;
use App\Services\VNPayService;
use App\Services\CouponService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller xử lý đặt vé cho người dùng
 */
class BookingController extends Controller
{
    /**
     * Hiển thị danh sách vé đã đặt của user
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Eager load relationships với select specific columns
        $bookings = $user->bookings()
            ->select(['id', 'user_id', 'schedule_id', 'quantity', 'contact_name', 'contact_phone', 'total_amount', 'discount_amount', 'final_price', 'status', 'created_at'])
            ->with([
                'schedule:id,tour_id,departure_date,max_people,available_slots,price',
                'schedule.tour:id,name,location,image',
                'payments' // Load payments để tính payment_status
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Hiển thị form đặt vé (redirect to payment page)
     */
    public function create(Schedule $schedule)
    {
        // Kiểm tra lịch trình còn hoạt động và còn chỗ
        if (!$schedule->is_active || $schedule->available_slots <= 0) {
            return redirect()->back()
                ->with('error', 'Lịch trình này không còn chỗ trống.');
        }

        // Kiểm tra ngày khởi hành chưa qua
        if ($schedule->departure_date < now()) {
            return redirect()->back()
                ->with('error', 'Lịch trình này đã qua ngày khởi hành.');
        }

        $tour = $schedule->tour;

        // Redirect thẳng đến trang checkout với schedule info
        return view('bookings.checkout', compact('schedule', 'tour'));
    }

    /**
     * Xử lý tạo đơn đặt vé mới
     */
    public function store(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'quantity' => 'required|integer|min:1',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email',
            'note' => 'nullable|string|max:500',
            'payment_type' => 'required|in:full,deposit',
            'coupon_code' => 'nullable|string|max:50',
        ], [
            'schedule_id.required' => 'Vui lòng chọn lịch trình.',
            'schedule_id.exists' => 'Lịch trình không tồn tại.',
            'quantity.required' => 'Vui lòng nhập số lượng người.',
            'quantity.min' => 'Số lượng phải ít nhất là 1.',
            'contact_name.required' => 'Vui lòng nhập tên liên hệ.',
            'contact_phone.required' => 'Vui lòng nhập số điện thoại.',
            'contact_email.required' => 'Vui lòng nhập email.',
            'contact_email.email' => 'Email không hợp lệ.',
            'payment_type.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        // Kiểm tra tour trùng lặp (phải cách 2 ngày sau khi tour trước kết thúc)
        $scheduleToBook = Schedule::findOrFail($request->schedule_id);
        
        if (Auth::check()) {
            $conflictBooking = Booking::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('schedule', function ($query) use ($scheduleToBook) {
                    // Kiểm tra nếu tour mới bắt đầu trước khi tour cũ kết thúc + 2 ngày
                    $query->whereRaw('DATE_ADD(COALESCE(end_date, departure_date), INTERVAL 2 DAY) > ?', [$scheduleToBook->departure_date]);
                })
                ->with('schedule.tour')
                ->first();

            if ($conflictBooking) {
                $oldTour = $conflictBooking->schedule;
                $endDate = $oldTour->end_date ?? $oldTour->departure_date;
                $allowedDate = date('d/m/Y', strtotime($endDate . ' +2 days'));
                
                return back()->withInput()
                    ->with('conflict_warning', "Bạn đã có tour \"{$conflictBooking->schedule->tour->name}\" từ {$oldTour->departure_date->format('d/m/Y')} đến " . ($oldTour->end_date ? $oldTour->end_date->format('d/m/Y') : $oldTour->departure_date->format('d/m/Y')) . ". Vui lòng đặt tour mới từ ngày {$allowedDate} trở đi.");
            }
        }

        // Khởi tạo services
        $couponService = new CouponService();
        $notificationService = new NotificationService();

        // Validation trước khi tạo booking
        $schedule = Schedule::find($request->schedule_id);
        if (!$schedule || !$schedule->price || $schedule->price <= 0) {
            return back()->withInput()->with('error', 'Lịch trình không hợp lệ hoặc chưa có giá.');
        }

        // Tính số tiền cần thanh toán trước để validate
        $pricePerPerson = $schedule->price;
        $totalAmount = $pricePerPerson * $request->quantity;
        $paymentType = $request->payment_type ?? 'full';
        
        // Tính tiền thanh toán dựa vào loại thanh toán
        if ($paymentType === 'full') {
            $amountToPay = $totalAmount;
        } else {
            $depositPercent = (int)($request->deposit_percent ?? 30);
            $amountToPay = round($totalAmount * $depositPercent / 100);
        }
        
        // Kiểm tra số tiền tối thiểu trước khi tạo booking
        if ($amountToPay < 5000) {
            return back()->withInput()->with('error', 'Số tiền thanh toán phải từ 5,000đ trở lên.');
        }

        // Sử dụng transaction với pessimistic locking để tránh race condition
        try {
            $booking = DB::transaction(function () use ($request, $couponService) {
                // Lock schedule row để tránh duplicate bookings (pessimistic locking)
                $schedule = Schedule::where('id', $request->schedule_id)
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    throw new \Exception('Lịch trình không tồn tại.');
                }

                // Kiểm tra số chỗ còn trống với lock
                if (!$schedule->hasAvailableSlots($request->quantity)) {
                    throw new \Exception('Số chỗ còn lại không đủ. Chỉ còn ' . $schedule->available_slots . ' chỗ.');
                }
                
                // Tính tổng tiền - lấy giá từ schedule
                if (!$schedule->price || $schedule->price <= 0) {
                    throw new \Exception('Giá tour chưa được thiết lập cho lịch trình này.');
                }
                $pricePerPerson = $schedule->price;
                $totalAmount = $pricePerPerson * $request->quantity;
                
                // Xử lý mã giảm giá
                $couponId = null;
                $discountAmount = 0;
                $finalPrice = $totalAmount;
                $appliedCoupon = null;
                
                if ($request->filled('coupon_code')) {
                    $couponResult = $couponService->validateAndGetCoupon($request->coupon_code, $totalAmount);
                    
                    if ($couponResult['success']) {
                        $appliedCoupon = $couponResult['coupon'];
                        $couponId = $appliedCoupon->id;
                        $discountAmount = $couponResult['discount'];
                        $finalPrice = $totalAmount - $discountAmount;
                        
                        // Tăng số lượt sử dụng coupon
                        $appliedCoupon->incrementUsage();
                    } else {
                        // Mã không hợp lệ - throw exception để rollback
                        throw new \Exception($couponResult['message']);
                    }
                }
            
                // Tính tiền thanh toán dựa vào loại thanh toán
                $depositPercent = 0;
                $depositAmount = 0;
                $paymentType = $request->payment_type ?? 'full';
                
                // Sử dụng final_price (sau giảm giá) để tính deposit
                $priceForPayment = $finalPrice;
                
                if ($paymentType === 'full') {
                    // Thanh toán đủ 100%
                    $paidAmount = $priceForPayment;
                    $depositPercent = 0;
                    $depositAmount = 0;
                } else {
                    // Đặt cọc 30% hoặc 50%
                    $depositPercent = (int)($request->deposit_percent ?? 30);
                    $depositAmount = round($priceForPayment * $depositPercent / 100);
                    $paidAmount = $depositAmount;
                }

                // Tạo booking với trạng thái pending
                $booking = Booking::create([
                    'user_id' => Auth::id() ?? 0,
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

                // Trừ số chỗ trống
                $schedule->decrement('available_slots', $request->quantity);
                
                // Trả về array chứa booking và thông tin thanh toán
                return [
                    'booking' => $booking,
                    'payment_type' => $paymentType,
                    'amount_to_pay' => $paidAmount,
                    'deposit_percent' => $depositPercent,
                    'has_coupon' => $couponId !== null,
                ];
            });
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }

        // Lấy booking object
        $bookingObj = $booking['booking'];
        $paymentType = $booking['payment_type'];
        $hasCoupon = $booking['has_coupon'] ?? false;

        // Gửi thông báo cho booking_manager
        $bookingObj->load(['schedule.tour', 'user', 'coupon']);
        $notificationService->notifyNewBooking($bookingObj);
        
        // Nếu có sử dụng coupon, gửi thông báo riêng
        if ($hasCoupon) {
            $notificationService->notifyBookingWithCoupon($bookingObj);
        }

        // Tính số tiền cần thanh toán (sử dụng final_price)
        $finalPrice = $bookingObj->final_price ?? $bookingObj->total_amount;
        if ($paymentType === 'deposit') {
            // Tính deposit amount từ request
            $depositPercent = (int)($booking['deposit_percent'] ?? 30);
            $amount = round($finalPrice * $depositPercent / 100);
        } else {
            $amount = $finalPrice;
        }
        
        $orderInfo = "Thanh toán booking #{$bookingObj->id} - Tour {$bookingObj->schedule->tour->name}";

        // Tạo URL thanh toán VNPay
        $vnpayService = new VNPayService();
        $paymentUrl = $vnpayService->createPaymentUrl($bookingObj, $amount, $orderInfo);

        return redirect($paymentUrl);
    }

    /**
     * Trang thanh toán giả lập (TEST MODE - Sandbox)
     */
    public function payment(Booking $booking)
    {
        // Kiểm tra quyền
        if ($booking->user_id !== (Auth::id() ?? 0)) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        // Nếu đã thanh toán rồi thì redirect
        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.show', $booking)
                ->with('info', 'Đơn này đã được thanh toán.');
        }

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Xử lý thanh toán sau khi nhập thông tin thẻ (TEST MODE)
     */
    public function processPayment(Request $request, Booking $booking)
    {
        // Kiểm tra quyền
        if ($booking->user_id !== (Auth::id() ?? 0)) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        // Validate thông tin thẻ (TEST MODE - chỉ kiểm tra có nhập hay không)
        $request->validate([
            'card_number' => 'required|string|min:13|max:19',
            'expiry_date' => 'required|string|size:5',
            'cvc' => 'required|string|min:3|max:4',
            'card_name' => 'required|string|max:255',
        ], [
            'card_number.required' => 'Vui lòng nhập số thẻ',
            'expiry_date.required' => 'Vui lòng nhập ngày hết hạn',
            'cvc.required' => 'Vui lòng nhập mã CVC/CVV',
            'card_name.required' => 'Vui lòng nhập tên chủ thẻ',
        ]);

        // Lấy thông tin từ session
        $paymentType = session('payment_type', 'full');
        $amountToPay = session('amount_to_pay', $booking->total_amount);

        // Giả lập thanh toán thành công (TEST MODE - luôn thành công)
        // Trong thực tế sẽ gọi API Stripe/VNPay/MoMo với thông tin thẻ
        
        DB::transaction(function () use ($booking, $paymentType, $amountToPay) {
            if ($paymentType === 'full') {
                // Thanh toán 100%
                $booking->update([
                    'paid_amount' => $booking->total_amount,
                    'payment_status' => 'paid',
                ]);
            } else {
                // Đặt cọc (30% hoặc 50%)
                $booking->update([
                    'paid_amount' => $booking->deposit_amount,
                    'payment_status' => 'partial',
                ]);
            }
        });

        // Xóa session
        session()->forget(['payment_type', 'amount_to_pay']);
        
        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Thanh toán thành công! Đơn hàng của bạn đang chờ xác nhận.');
    }

    /**
     * Xem chi tiết đơn đặt vé
     */
    public function show(Booking $booking)
    {
        // Kiểm tra booking có thuộc về user hiện tại
        if ($booking->user_id !== (Auth::id() ?? 0)) {
            abort(403, 'Bạn không có quyền xem đơn này.');
        }

        $booking->load(['schedule.tour']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Hủy đơn đặt vé
     * - Pending: có thể hủy tự do
     * - Confirmed: cần nhập lý do hủy và sẽ chuyển sang trạng thái refunded
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Kiểm tra booking có thuộc về user hiện tại
        if ($booking->user_id !== (Auth::id() ?? 0)) {
            abort(403, 'Bạn không có quyền hủy đơn này.');
        }

        $status = $booking->status;
        
        // Không cho hủy nếu đã hoàn thành hoặc đã hủy
        if (in_array($status, ['completed', 'cancelled', 'refunded'])) {
            return back()->with('error', 'Không thể hủy đơn ở trạng thái này.');
        }

        // Nếu đơn đã confirmed, bắt buộc nhập lý do
        if ($status === 'confirmed') {
            $request->validate([
                'cancellation_reason' => 'required|string|min:10|max:500',
            ], [
                'cancellation_reason.required' => 'Vui lòng nhập lý do hủy đơn.',
                'cancellation_reason.min' => 'Lý do hủy phải có ít nhất 10 ký tự.',
                'cancellation_reason.max' => 'Lý do hủy không được quá 500 ký tự.',
            ]);
        }

        $couponService = new CouponService();
        $notificationService = new \App\Services\NotificationService();
        $currentUser = Auth::user();

        // Kiểm tra xem có thanh toán VNPay thành công không
        $paidAmount = $booking->paid_amount;
        $vnpayPayment = $booking->payments()
            ->where('payment_method', 'vnpay')
            ->where('status', 'success')
            ->first();

        if ($paidAmount > 0 && $vnpayPayment) {
            // Có thanh toán VNPay -> chuyển sang refund_processing
            DB::transaction(function () use ($booking, $couponService, $request, $status, $notificationService, $currentUser) {
                // Hoàn lại số chỗ nếu chưa hoàn thành
                if (in_array($status, ['pending', 'confirmed'])) {
                    $booking->schedule->increment('available_slots', $booking->quantity);
                }

                // Hoàn lại lượt sử dụng coupon nếu có
                if ($booking->coupon_id && $booking->coupon) {
                    $couponService->revertCouponUsage($booking->coupon);
                }

                // Cập nhật trạng thái booking
                $booking->update([
                    'status' => 'refund_processing',
                    'cancellation_reason' => $request->input('cancellation_reason'),
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'refund_status' => 'processing',
                ]);

                // Gửi thông báo
                $booking->load(['schedule.tour']);
                $notificationService->notifyBookingCancelled($booking, $currentUser, false);
                $notificationService->notifyRefundProcessing($booking);
            });

            // Gọi VNPay Refund API
            $this->processVnpayRefundWeb($booking, $paidAmount, $vnpayPayment, $notificationService);

            $booking->refresh();
            if ($booking->refund_status === 'success') {
                return back()->with('success', 'Đơn đặt vé đã được hủy và hoàn tiền thành công qua VNPay.');
            } else {
                return back()->with('warning', 'Đơn đặt vé đã được hủy. Hoàn tiền VNPay: ' . ($booking->refund_message ?? 'Đang xử lý') . '. Vui lòng liên hệ hỗ trợ nếu cần.');
            }
        } else {
            // Không có thanh toán VNPay -> hủy bình thường
            DB::transaction(function () use ($booking, $couponService, $request, $status, $notificationService, $currentUser, $paidAmount) {
                // Hoàn lại số chỗ nếu chưa hoàn thành
                if (in_array($status, ['pending', 'confirmed'])) {
                    $booking->schedule->increment('available_slots', $booking->quantity);
                }

                // Hoàn lại lượt sử dụng coupon nếu có
                if ($booking->coupon_id && $booking->coupon) {
                    $couponService->revertCouponUsage($booking->coupon);
                }

                // Cập nhật trạng thái booking
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->input('cancellation_reason'),
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                ]);
                
                // Nếu đã thanh toán bằng tiền mặt/chuyển khoản, ghi nhận
                if ($paidAmount > 0) {
                    $booking->payments()->create([
                        'payment_type' => 'full',
                        'payment_method' => 'transfer',
                        'amount' => $paidAmount,
                        'status' => 'refunded',
                        'note' => 'Hoàn tiền do khách hủy đơn: ' . $request->input('cancellation_reason'),
                        'confirmed_at' => now(),
                    ]);
                }

                // Gửi thông báo cho booking_manager
                $notificationService->notifyBookingCancelled($booking, $currentUser, false);
            });

            $message = $status === 'confirmed' 
                ? 'Đơn đặt vé đã được hủy.'
                : 'Đã hủy đơn đặt vé thành công.';

            return back()->with('success', $message);
        }
    }

    /**
     * Xử lý hoàn tiền VNPay cho web user
     */
    protected function processVnpayRefundWeb(Booking $booking, float $amount, $vnpayPayment, $notificationService): void
    {
        try {
            $vnPayService = new VNPayService();
            
            // Lấy tất cả các payment VNPay thành công để hoàn tiền từng giao dịch
            $vnpayPayments = $booking->payments()
                ->where('payment_method', 'vnpay')
                ->where('status', 'success')
                ->get();

            $allSuccess = true;
            $lastMessage = '';

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

                $result = $vnPayService->refund(
                    $booking,
                    $refundAmount,
                    $transactionNo,
                    $txnRef,
                    $transactionDate,
                    Auth::user()->name ?? 'User'
                );

                if ($result['success']) {
                    // Cập nhật payment gốc thành refunded
                    $payment->update(['status' => 'refunded']);
                    
                    // Tạo record hoàn tiền
                    $booking->payments()->create([
                        'payment_type' => $payment->payment_type,
                        'payment_method' => 'vnpay',
                        'amount' => $refundAmount,
                        'status' => 'refunded',
                        'transaction_ref' => $result['data']['vnp_TransactionNo'] ?? null,
                        'note' => 'Hoàn tiền VNPay thành công: ' . $result['message'],
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
                        'note' => 'Hoàn tiền VNPay thất bại: ' . $result['message'],
                        'confirmed_at' => now(),
                    ]);
                    break; // Dừng nếu có lỗi
                }
            }

            $booking->load(['schedule.tour']);

            if ($allSuccess) {
                $booking->update([
                    'status' => 'refunded',
                    'refund_status' => 'success',
                    'refund_message' => $lastMessage,
                    'refund_transaction_ref' => $result['data']['vnp_TransactionNo'] ?? null,
                    'refund_processed_at' => now(),
                ]);

                $notificationService->notifyRefundCompleted($booking, true, $lastMessage);
            } else {
                $booking->update([
                    'status' => 'cancelled',
                    'refund_status' => 'failed',
                    'refund_message' => $lastMessage,
                    'refund_processed_at' => now(),
                ]);

                $notificationService->notifyRefundCompleted($booking, false, $lastMessage);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Web VNPay Refund Error: ' . $e->getMessage());

            $booking->update([
                'status' => 'cancelled',
                'refund_status' => 'failed',
                'refund_message' => 'Lỗi xử lý: ' . $e->getMessage(),
                'refund_processed_at' => now(),
            ]);

            $notificationService->notifyRefundCompleted($booking, false, 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý thanh toán VNPay
     */
    public function processPaymentVNPay(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Kiểm tra quyền
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Lấy loại thanh toán từ form (full hoặc deposit)
        $paymentType = $request->input('payment_type', 'full');
        
        // Tính số tiền cần thanh toán
        if ($paymentType === 'deposit') {
            // Đặt cọc 30%
            $amount = round($booking->total_amount * 0.3);
            
            // Cập nhật thông tin deposit vào booking
            $booking->update([
                'deposit_percent' => 30,
                'deposit_amount' => $amount,
            ]);
        } else {
            // Thanh toán 100%
            $amount = $booking->total_amount;
            
            // Đảm bảo deposit là 0 cho thanh toán full
            $booking->update([
                'deposit_percent' => 0,
                'deposit_amount' => 0,
            ]);
        }
        
        // Kiểm tra số tiền tối thiểu (VNPay yêu cầu tối thiểu 5,000đ)
        if ($amount < 5000) {
            return back()->with('error', 'Số tiền thanh toán phải từ 5,000đ trở lên. Vui lòng chọn phương thức thanh toán khác hoặc liên hệ admin.');
        }
        
        $orderInfo = "Thanh toán booking #{$booking->id} - Tour {$booking->schedule->tour->name}";

        // Tạo URL thanh toán
        $vnpayService = new VNPayService();
        $paymentUrl = $vnpayService->createPaymentUrl($booking, $amount, $orderInfo);

        return redirect($paymentUrl);
    }

    /**
     * Trang thành công sau khi thanh toán
     */
    public function success(Booking $booking)
    {
        // Kiểm tra quyền
        if ($booking->user_id !== (Auth::id() ?? 0)) {
            abort(403, 'Bạn không có quyền xem trang này.');
        }

        // Kiểm tra đã thanh toán chưa
        if ($booking->payment_status === 'unpaid') {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'Đơn hàng chưa được thanh toán.');
        }

        $booking->load(['schedule.tour']);

        return view('bookings.success', compact('booking'));
    }

    /**
     * Callback từ VNPay sau khi thanh toán
     */
    public function vnpayCallback(Request $request)
    {
        $vnpayService = new VNPayService();
        
        // Xác thực callback
        if (!$vnpayService->validateCallback($request)) {
            return redirect()->route('bookings.index')
                ->with('error', 'Giao dịch không hợp lệ!');
        }

        $vnp_ResponseCode = $request->vnp_ResponseCode;
        $vnp_TxnRef = $request->vnp_TxnRef;
        $bookingId = $vnpayService->parseBookingId($vnp_TxnRef);
        
        $booking = Booking::findOrFail($bookingId);
        $booking->load(['schedule.tour', 'user', 'coupon']);
        
        $notificationService = new NotificationService();

        if ($vnp_ResponseCode == '00') {
            // Thanh toán thành công
            $paidAmount = (float) ($request->vnp_Amount / 100); // VNPay trả về đơn vị x100
            
            // Xác định loại thanh toán (deposit hay full)
            $paymentType = $paidAmount < $booking->final_price ? 'deposit' : 'full';
            
            // Tạo payment record - lưu cả vnp_TxnRef, vnp_TransactionNo và vnp_PayDate
            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_type' => $paymentType,
                'payment_method' => 'vnpay',
                'amount' => $paidAmount,
                'status' => 'success',
                'transaction_ref' => $vnp_TxnRef,
                'vnp_transaction_no' => $request->vnp_TransactionNo ?? null,
                'vnp_pay_date' => $request->vnp_PayDate ?? null,
                'vnpay_response_code' => $vnp_ResponseCode,
                'note' => 'Thanh toán qua VNPay',
            ]);
            
            // Giữ trạng thái booking là 'pending' - chờ admin xác nhận
            // Booking đã được tạo với status 'pending' từ trước

            // Gửi thông báo thanh toán thành công
            $notificationService->notifyPaymentSuccess($booking);

            return redirect()->route('bookings.success', $booking->id)
                ->with('success', 'Thanh toán thành công! Đơn hàng đang chờ xác nhận từ quản trị viên.');
        } else {
            // Thanh toán thất bại - tạo payment record với status failed
            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'full',
                'payment_method' => 'vnpay',
                'amount' => 0,
                'status' => 'failed',
                'transaction_ref' => $vnp_TxnRef,
                'vnpay_response_code' => $vnp_ResponseCode,
                'note' => 'Thanh toán thất bại: ' . $this->getVNPayErrorMessage($vnp_ResponseCode),
            ]);
            
            $errorMessage = $this->getVNPayErrorMessage($vnp_ResponseCode);
            
            // Gửi thông báo thanh toán thất bại
            $notificationService->notifyPaymentFailed($booking, $vnp_ResponseCode, $errorMessage);

            // Nếu người dùng hủy giao dịch (mã 24), quay về trang đặt vé
            if ($vnp_ResponseCode == '24') {
                return redirect()->route('bookings.create', ['schedule' => $booking->schedule_id])
                    ->with('warning', 'Bạn đã hủy thanh toán. Vui lòng thử lại nếu muốn tiếp tục đặt vé.');
            }

            return redirect()->route('bookings.payment', $booking->id)
                ->with('error', 'Thanh toán thất bại! ' . $errorMessage);
        }
    }

    /**
     * Lấy thông báo lỗi VNPay theo mã
     */
    protected function getVNPayErrorMessage(string $responseCode): string
    {
        $messages = [
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Khách hàng hủy giao dịch.',
            '51' => 'Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Quý khách nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định. Vui lòng liên hệ hỗ trợ.',
        ];

        return $messages[$responseCode] ?? 'Vui lòng thử lại hoặc liên hệ hỗ trợ.';
    }
}
