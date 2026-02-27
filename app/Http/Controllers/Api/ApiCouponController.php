<?php

namespace App\Http\Controllers\Api;

use App\Models\Coupon;
use App\Http\Resources\CouponResource;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiCouponController extends ApiController
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Get available coupons
     */
    public function available(Request $request)
    {
        $coupons = Coupon::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            CouponResource::collection($coupons),
            'Lấy danh sách mã giảm giá thành công'
        );
    }

    /**
     * Validate coupon code
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'order_amount' => ['required', 'numeric', 'min:0'],
        ], [
            'code.required' => 'Vui lòng nhập mã giảm giá',
            'order_amount.required' => 'Vui lòng cung cấp số tiền đơn hàng',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $coupon = Coupon::where('code', $request->code)->first();

            if (!$coupon) {
                return $this->errorResponse(
                    'Mã giảm giá không tồn tại',
                    null,
                    'COUPON_NOT_FOUND',
                    404
                );
            }

            // Check if valid
            if (!$coupon->is_valid) {
                $reason = 'Mã giảm giá không hợp lệ';
                
                if (!$coupon->is_active) {
                    $reason = 'Mã giảm giá đã bị vô hiệu hóa';
                } elseif ($coupon->start_date > now()) {
                    $reason = 'Mã giảm giá chưa bắt đầu';
                } elseif ($coupon->end_date < now()) {
                    $reason = 'Mã giảm giá đã hết hạn';
                } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    $reason = 'Mã giảm giá đã hết lượt sử dụng';
                }

                return $this->errorResponse(
                    $reason,
                    null,
                    'COUPON_INVALID',
                    400
                );
            }

            // Check minimum order amount
            if ($request->order_amount < $coupon->min_order_amount) {
                return $this->errorResponse(
                    "Đơn hàng tối thiểu phải từ " . number_format($coupon->min_order_amount, 0, ',', '.') . " VNĐ",
                    null,
                    'ORDER_AMOUNT_TOO_LOW',
                    400
                );
            }

            // Check minimum level required
            if ($coupon->min_level_required > 0) {
                $user = Auth::user();
                $userLevel = $user->current_level ?? 1;
                if ($userLevel < $coupon->min_level_required) {
                    return $this->errorResponse(
                        'Mã giảm giá này yêu cầu Level ' . $coupon->min_level_required . ' trở lên. Level hiện tại của bạn: ' . $userLevel,
                        null,
                        'COUPON_LEVEL_REQUIRED',
                        400
                    );
                }
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($request->order_amount);

            return $this->successResponse([
                'coupon' => new CouponResource($coupon),
                'discount_amount' => (float) $discountAmount,
                'final_amount' => (float) ($request->order_amount - $discountAmount),
            ], 'Mã giảm giá hợp lệ');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Apply coupon (used in checkout context)
     */
    public function apply(Request $request)
    {
        return $this->validate($request);
    }
}
