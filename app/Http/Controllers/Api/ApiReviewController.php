<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Tour;
use App\Models\Booking;
use App\Http\Resources\ReviewResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiReviewController extends ApiController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request)
    {
        $query = Auth::user()->reviews()
            ->with(['tour', 'booking'])
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 15);
        $reviews = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            ReviewResource::collection($reviews),
            $this->getPaginationMeta($reviews),
            'Lấy danh sách đánh giá thành công'
        );
    }

    /**
     * Create new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => ['required', 'exists:tours,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string', 'max:1000'],
        ], [
            'tour_id.required' => 'Vui lòng chọn tour',
            'rating.required' => 'Vui lòng chọn số sao',
            'rating.min' => 'Số sao tối thiểu là 1',
            'rating.max' => 'Số sao tối đa là 5',
            'title.required' => 'Vui lòng nhập tiêu đề',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $tour = Tour::findOrFail($request->tour_id);

            // Check if user has completed booking for this tour
            $hasCompletedBooking = Booking::where('user_id', Auth::id())
                ->whereHas('schedule', function($q) use ($request) {
                    $q->where('tour_id', $request->tour_id);
                })
                ->where('status', 'completed')
                ->exists();

            if (!$hasCompletedBooking) {
                return $this->errorResponse(
                    'Bạn chỉ có thể đánh giá tour sau khi hoàn thành chuyến đi',
                    null,
                    'NO_COMPLETED_BOOKING',
                    403
                );
            }

            // Check if already reviewed
            $existingReview = Review::where('user_id', Auth::id())
                ->where('tour_id', $request->tour_id)
                ->first();

            if ($existingReview) {
                return $this->errorResponse(
                    'Bạn đã đánh giá tour này rồi',
                    null,
                    'ALREADY_REVIEWED',
                    400
                );
            }

            // Create review
            $review = Review::create([
                'user_id' => Auth::id(),
                'tour_id' => $request->tour_id,
                'booking_id' => $request->booking_id,
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'status' => 'pending', // Reviews need approval
            ]);

            // Send notification to admin
            try {
                $this->notificationService->notifyNewReview($review);
            } catch (\Exception $e) {
                Log::error('Failed to send review notification: ' . $e->getMessage());
            }

            $review->load(['tour', 'user']);

            return $this->successResponse(
                new ReviewResource($review),
                'Đánh giá của bạn đã được gửi và đang chờ duyệt',
                201
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi tạo đánh giá: ' . $e->getMessage());
        }
    }

    /**
     * Update review
     */
    public function update(Request $request, Review $review)
    {
        // Check authorization
        if ($review->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền sửa đánh giá này');
        }

        // Only allow editing pending or approved reviews
        if (!in_array($review->status, ['pending', 'approved'])) {
            return $this->errorResponse(
                'Không thể sửa đánh giá này',
                null,
                'CANNOT_EDIT',
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $review->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'status' => 'pending', // Reset to pending after edit
            ]);

            $review->load(['tour', 'user']);

            return $this->successResponse(
                new ReviewResource($review),
                'Cập nhật đánh giá thành công'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi cập nhật đánh giá');
        }
    }

    /**
     * Delete review
     */
    public function destroy(Review $review)
    {
        // Check authorization
        if ($review->user_id !== Auth::id()) {
            return $this->forbiddenResponse('Bạn không có quyền xóa đánh giá này');
        }

        try {
            $review->delete();

            return $this->successResponse(null, 'Xóa đánh giá thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi xóa đánh giá');
        }
    }
}
