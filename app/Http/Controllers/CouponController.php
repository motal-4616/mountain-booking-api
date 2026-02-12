<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller xử lý mã giảm giá cho người dùng (AJAX)
 */
class CouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Validate và áp dụng mã giảm giá (AJAX)
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $code = $request->input('code');
        $totalAmount = (float) $request->input('total_amount');

        $result = $this->couponService->validateAndGetCoupon($code, $totalAmount);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        $coupon = $result['coupon'];
        $discount = $result['discount'];
        $finalPrice = $totalAmount - $discount;

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'coupon_name' => $coupon->name,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount_amount' => $discount,
                'final_price' => $finalPrice,
                'formatted_discount' => number_format($discount, 0, ',', '.') . 'đ',
                'formatted_final_price' => number_format($finalPrice, 0, ',', '.') . 'đ',
            ],
        ]);
    }

    /**
     * Xóa mã giảm giá đã áp dụng (AJAX)
     */
    public function remove(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
        ]);
    }
}
