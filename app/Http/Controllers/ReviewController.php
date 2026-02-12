<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Hiển thị form tạo đánh giá cho tour
     */
    public function create($tourId)
    {
        $tour = Tour::findOrFail($tourId);
        
        // Kiểm tra user đã hoàn thành tour này chưa (tour đã qua ngày kết thúc)
        $completedBooking = Booking::where('user_id', Auth::id())
            ->whereHas('schedule', function($q) use ($tourId) {
                $q->where('tour_id', $tourId)
                  ->where(function($query) {
                      $query->whereNotNull('end_date')
                            ->whereRaw('end_date < CURDATE()')
                            ->orWhere(function($q2) {
                                $q2->whereNull('end_date')
                                   ->whereRaw('departure_date < CURDATE()');
                            });
                  });
            })
            ->whereIn('payment_status', ['paid', 'partial'])
            ->first();

        if (!$completedBooking) {
            return redirect()->route('tours.show', $tourId)
                ->with('error', 'Bạn cần hoàn thành tour này trước khi đánh giá.');
        }

        // Kiểm tra đã đánh giá chưa
        $existingReview = Review::where('user_id', Auth::id())
            ->where('tour_id', $tourId)
            ->first();

        if ($existingReview) {
            return redirect()->route('tours.show', $tourId)
                ->with('info', 'Bạn đã đánh giá tour này rồi.');
        }

        return view('reviews.create', compact('tour', 'completedBooking'));
    }

    /**
     * Hiển thị form tạo đánh giá từ booking
     */
    public function createFromBooking(Booking $booking)
    {
        // Kiểm tra quyền
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $booking->load(['schedule.tour']);
        $tour = $booking->schedule->tour;
        
        // Kiểm tra tour đã kết thúc chưa
        $endDate = $booking->schedule->end_date ?? $booking->schedule->departure_date;
        if ($endDate >= now()->toDateString()) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'Tour chưa kết thúc, bạn chưa thể đánh giá.');
        }

        // Kiểm tra đã thanh toán chưa
        if (!in_array($booking->payment_status, ['paid', 'partial'])) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'Bạn cần thanh toán trước khi đánh giá.');
        }

        // Kiểm tra đã đánh giá booking này chưa
        $existingReview = Review::where('user_id', Auth::id())
            ->where('booking_id', $booking->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('info', 'Bạn đã đánh giá cho đơn hàng này rồi.');
        }

        return view('reviews.create', compact('tour', 'booking'));
    }

    /**
     * Lưu đánh giá mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.min' => 'Đánh giá tối thiểu 1 sao',
            'rating.max' => 'Đánh giá tối đa 5 sao',
            'title.max' => 'Tiêu đề không được quá 255 ký tự',
            'comment.max' => 'Nhận xét không được quá 1000 ký tự',
        ]);

        // Kiểm tra đã đánh giá booking này chưa (nếu có booking_id)
        if ($request->booking_id) {
            $existingReview = Review::where('user_id', Auth::id())
                ->where('booking_id', $request->booking_id)
                ->first();

            if ($existingReview) {
                return redirect()->back()->with('error', 'Bạn đã đánh giá cho đơn hàng này rồi.');
            }
        } else {
            // Kiểm tra đã đánh giá tour này chưa
            $existingReview = Review::where('user_id', Auth::id())
                ->where('tour_id', $request->tour_id)
                ->whereNull('booking_id')
                ->first();

            if ($existingReview) {
                return redirect()->back()->with('error', 'Bạn đã đánh giá tour này rồi.');
            }
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'tour_id' => $validated['tour_id'],
            'booking_id' => $validated['booking_id'] ?? null,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'status' => 'pending', // Mặc định chờ duyệt
        ]);

        // Gửi thông báo cho booking_manager để duyệt
        $review->load(['tour', 'user']);
        $notificationService = new NotificationService();
        $notificationService->notifyNewReview($review);

        // Redirect về trang phù hợp
        if ($request->booking_id) {
            return redirect()->route('bookings.show', $request->booking_id)
                ->with('success', 'Cảm ơn bạn đã đánh giá! Đánh giá sẽ được hiển thị sau khi admin duyệt.');
        }

        return redirect()->route('tours.show', $validated['tour_id'])
            ->with('success', 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn sẽ được hiển thị sau khi admin duyệt.');
    }

    /**
     * Hiển thị danh sách đánh giá của user
     */
    public function myReviews()
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['tour', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reviews.my-reviews', compact('reviews'));
    }

    /**
     * Xóa đánh giá của chính mình
     */
    public function destroy($id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}
