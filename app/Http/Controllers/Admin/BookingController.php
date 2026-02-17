<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Services\VNPayService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller quản lý Đơn đặt vé (Admin)
 */
class BookingController extends Controller
{
    /**
     * Danh sách đơn đặt vé với bộ lọc
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'schedule.tour', 'payments']);

        // Lọc theo trạng thái đơn
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo trạng thái thanh toán - cần xử lý đặc biệt vì payment_status là computed
        // Tạm thời bỏ filter này hoặc dùng subquery

        // Tìm kiếm theo tên, email, phone, mã đơn
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Xem chi tiết đơn đặt vé
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'schedule.tour', 'payments.confirmedByUser']);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Xác nhận đơn đặt vé
     */
    public function confirm(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể xác nhận đơn đang chờ.');
        }

        $booking->update(['status' => 'confirmed']);

        // Gửi thông báo cho user
        $booking->load(['user', 'schedule.tour']);
        $notificationService = new NotificationService();
        $notificationService->notifyBookingConfirmed($booking);

        return back()->with('success', 'Đã xác nhận đơn đặt vé #' . $booking->id);
    }

    /**
     * Hủy đơn đặt vé
     * - Pending: có thể hủy tự do
     * - Confirmed: cần nhập lý do hủy và sẽ chuyển sang trạng thái refunded
     */
    public function cancel(Request $request, Booking $booking)
    {
        $status = $booking->status;
        
        // Không cho hủy nếu đã hoàn thành hoặc đã hủy
        if (in_array($status, ['completed', 'cancelled', 'refunded', 'refund_processing'])) {
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

        $notificationService = new NotificationService();
        $currentAdmin = Auth::user();

        // Kiểm tra xem có thanh toán VNPay thành công không
        $paidAmount = $booking->paid_amount;
        $vnpayPayment = $booking->payments()
            ->where('payment_method', 'vnpay')
            ->where('status', 'success')
            ->first();

        if ($paidAmount > 0 && $vnpayPayment) {
            // Có thanh toán VNPay -> chuyển sang refund_processing
            DB::transaction(function () use ($booking, $request, $status, $notificationService, $currentAdmin) {
                // Hoàn lại số chỗ
                if (in_array($status, ['pending', 'confirmed'])) {
                    $booking->schedule->increment('available_slots', $booking->quantity);
                }

                // Cập nhật trạng thái booking
                $booking->update([
                    'status' => 'refund_processing',
                    'cancellation_reason' => $request->input('cancellation_reason'),
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'refund_status' => 'processing',
                ]);

                // Gửi thông báo cho user
                $booking->load(['schedule.tour']);
                $notificationService->notifyBookingCancelled($booking, $currentAdmin, true);
                $notificationService->notifyRefundProcessing($booking);
            });

            // Gọi VNPay Refund API (sau khi commit)
            $this->processVnpayRefundAdmin($booking, $paidAmount, $vnpayPayment, $notificationService);

            $booking->refresh();
            $refundStatus = $booking->refund_status;
            
            if ($refundStatus === 'success') {
                return back()->with('success', 'Đã hủy đơn #' . $booking->id . ' và hoàn tiền thành công qua VNPay.');
            } else {
                // Debug: hiển thị chi tiết lỗi refund
                $debugInfo = $this->getRefundDebugInfo($booking, $vnpayPayment);
                return back()->with('warning', 'Đã hủy đơn #' . $booking->id . '. Hoàn tiền VNPay thất bại: ' . $booking->refund_message . '. Cần xử lý thủ công.')
                             ->with('refund_debug', $debugInfo);
            }
        } else {
            // Không có thanh toán VNPay -> hủy bình thường
            DB::transaction(function () use ($booking, $request, $status, $notificationService, $currentAdmin, $paidAmount) {
                // Hoàn lại số chỗ
                if (in_array($status, ['pending', 'confirmed'])) {
                    $booking->schedule->increment('available_slots', $booking->quantity);
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
                        'note' => 'Hoàn tiền do admin hủy đơn: ' . $request->input('cancellation_reason'),
                        'confirmed_by' => Auth::id(),
                        'confirmed_at' => now(),
                    ]);
                }

                // Gửi thông báo cho user
                $notificationService->notifyBookingCancelled($booking, $currentAdmin, true);
            });

            $message = $status === 'confirmed' 
                ? 'Đã hủy đơn đặt vé #' . $booking->id . '.'
                : 'Đã hủy đơn đặt vé #' . $booking->id;

            return back()->with('success', $message);
        }
    }

    /**
     * Xử lý hoàn tiền VNPay cho admin
     */
    protected function processVnpayRefundAdmin(Booking $booking, float $amount, $vnpayPayment, NotificationService $notificationService): void
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

                $result = $vnPayService->refund(
                    $booking,
                    $refundAmount,
                    $transactionNo,
                    $txnRef,
                    $transactionDate,
                    Auth::user()->name ?? 'Admin'
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
                        'note' => 'Hoàn tiền VNPay thành công: ' . $result['message'],
                        'confirmed_by' => Auth::id(),
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
                        'note' => 'Hoàn tiền VNPay thất bại: ' . $result['message'] . '. Cần xử lý thủ công.',
                        'confirmed_by' => Auth::id(),
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
                    'refund_transaction_ref' => $lastResult['data']['vnp_TransactionNo'] ?? null,
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
            Log::error('Admin VNPay Refund Error: ' . $e->getMessage());

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
     * Xác nhận hoàn tiền thủ công (admin đã hoàn tiền cho khách qua kênh khác)
     */
    public function manualRefund(Request $request, Booking $booking)
    {
        // Chỉ cho phép khi refund đã thất bại hoặc đơn đã hủy nhưng chưa hoàn tiền
        if (!in_array($booking->refund_status, ['failed', null]) || !in_array($booking->status, ['cancelled', 'refund_processing'])) {
            return back()->with('error', 'Không thể thực hiện xác nhận hoàn tiền thủ công cho đơn này.');
        }

        $request->validate([
            'refund_note' => 'required|string|min:5|max:500',
        ], [
            'refund_note.required' => 'Vui lòng nhập ghi chú hoàn tiền.',
            'refund_note.min' => 'Ghi chú hoàn tiền phải có ít nhất 5 ký tự.',
        ]);

        $paidAmount = $booking->paid_amount;

        DB::transaction(function () use ($booking, $request, $paidAmount) {
            // Giữ nguyên payment gốc status='success' (Đã thanh toán)

            // Xóa record refund thất bại cũ
            $booking->payments()
                ->where('payment_method', 'vnpay')
                ->where('status', 'failed')
                ->whereNotNull('note')
                ->where('note', 'like', '%Hoàn tiền%')
                ->delete();

            // Tạo record refund thành công
            $booking->payments()->create([
                'payment_type' => 'full',
                'payment_method' => 'transfer',
                'amount' => $paidAmount,
                'status' => 'refunded',
                'note' => 'Hoàn tiền thủ công bởi Admin: ' . $request->input('refund_note'),
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            // Cập nhật trạng thái booking
            $booking->update([
                'status' => 'refunded',
                'refund_status' => 'success',
                'refund_message' => 'Hoàn tiền thủ công: ' . $request->input('refund_note'),
                'refund_processed_at' => now(),
            ]);
        });

        // Gửi thông báo
        try {
            $booking->load(['schedule.tour']);
            $notificationService = new NotificationService();
            $notificationService->notifyRefundCompleted($booking, true, 'Hoàn tiền thủ công thành công');
        } catch (\Exception $e) {
            Log::warning('Failed to send refund notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận hoàn tiền thủ công ' . number_format((float)$paidAmount, 0, ',', '.') . 'đ cho đơn #' . $booking->id);
    }

    /**
     * Xóa đơn đặt vé
     */
    public function destroy(Booking $booking)
    {
        // Chỉ cho xóa đơn đã hủy hoặc đã hoàn tiền
        if (!in_array($booking->status, ['cancelled', 'refunded', 'refund_processing'])) {
            return back()->with('error', 'Chỉ có thể xóa đơn đã hủy hoặc đã hoàn tiền.');
        }

        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Xóa đơn đặt vé thành công!');
    }

    /**
     * Thử lại hoàn tiền VNPay (khi hoàn tiền thất bại trước đó)
     */
    public function retryRefund(Booking $booking)
    {
        // Chỉ cho retry khi refund đã thất bại
        if ($booking->refund_status !== 'failed') {
            return back()->with('error', 'Chỉ có thể thử lại khi hoàn tiền đã thất bại.');
        }

        // Tìm payment VNPay gốc còn status success (chưa được refund)
        $vnpayPayment = $booking->payments()
            ->where('payment_method', 'vnpay')
            ->where('status', 'success')
            ->first();

        if (!$vnpayPayment) {
            return back()->with('error', 'Không tìm thấy giao dịch VNPay gốc để hoàn tiền.');
        }

        $paidAmount = $booking->paid_amount;
        $notificationService = new NotificationService();

        // Xóa record refund thất bại cũ
        $booking->payments()
            ->where('payment_method', 'vnpay')
            ->where('status', 'failed')
            ->whereNotNull('note')
            ->where('note', 'like', '%Hoàn tiền%')
            ->delete();

        // Cập nhật trạng thái 
        $booking->update([
            'status' => 'refund_processing',
            'refund_status' => 'processing',
            'refund_message' => null,
        ]);

        // Gọi lại VNPay Refund API
        $this->processVnpayRefundAdmin($booking, $paidAmount, $vnpayPayment, $notificationService);

        $booking->refresh();
        $refundStatus = $booking->refund_status;

        if ($refundStatus === 'success') {
            return back()->with('success', 'Thử lại hoàn tiền thành công cho đơn #' . $booking->id);
        } else {
            $debugInfo = $this->getRefundDebugInfo($booking, $vnpayPayment);
            return back()->with('warning', 'Thử lại hoàn tiền vẫn thất bại: ' . $booking->refund_message)
                         ->with('refund_debug', $debugInfo);
        }
    }

    /**
     * Xác nhận thanh toán phần còn lại tại điểm tập kết
     * (Admin xác nhận khách đã trả đủ tiền tại điểm tập kết)
     */
    public function confirmPayment(Request $request, Booking $booking)
    {
        // Kiểm tra điều kiện
        if ($booking->isFullyPaid()) {
            return back()->with('error', 'Đơn này đã được thanh toán đầy đủ.');
        }

        if ($booking->paid_amount <= 0) {
            return back()->with('error', 'Khách chưa thanh toán cọc online. Không thể xác nhận.');
        }

        $remainingAmount = $booking->remaining_amount;

        // Tạo payment record cho phần còn lại
        DB::transaction(function () use ($booking, $remainingAmount, $request) {
            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'remaining',
                'payment_method' => $request->input('payment_method', 'cash'), // cash hoặc transfer
                'amount' => $remainingAmount,
                'status' => 'success',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
                'note' => 'Admin xác nhận thanh toán tại điểm tập kết',
            ]);

            // Cập nhật trạng thái booking
            $booking->update(['status' => 'confirmed']);
        });

        return back()->with('success', 'Đã xác nhận khách thanh toán đủ ' . number_format((float)$remainingAmount, 0, ',', '.') . 'đ tại điểm tập kết.');
    }

    /**
     * Lấy debug info cho refund (hiển thị khi hoàn tiền thất bại)
     */
    protected function getRefundDebugInfo(Booking $booking, $vnpayPayment): array
    {
        $payments = $booking->payments()
            ->where('payment_method', 'vnpay')
            ->where('status', 'success')
            ->get();

        $debugPayments = $payments->map(function ($p) {
            return [
                'id' => $p->id,
                'amount' => $p->amount,
                'transaction_ref (vnp_TxnRef)' => $p->transaction_ref,
                'vnp_transaction_no' => $p->vnp_transaction_no,
                'created_at' => $p->created_at?->format('YmdHis'),
                'status' => $p->status,
            ];
        })->toArray();

        // Đọc log gần nhất
        $logFile = storage_path('logs/laravel.log');
        $recentLogs = '';
        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile), -100);
            $refundLogs = array_filter($lines, fn($l) => str_contains($l, 'VNPay Refund') && str_contains($l, (string)$booking->id));
            $recentLogs = implode("\n", array_slice($refundLogs, -6));
        }

        return [
            'booking_id' => $booking->id,
            'paid_amount' => $booking->paid_amount,
            'refund_status' => $booking->refund_status,
            'refund_message' => $booking->refund_message,
            'payments' => $debugPayments,
            'recent_logs' => $recentLogs,
        ];
    }
}
