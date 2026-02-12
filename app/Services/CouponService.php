<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service xử lý logic mã giảm giá
 */
class CouponService
{
    /**
     * Validate và lấy thông tin coupon từ mã
     * 
     * @param string $code Mã giảm giá
     * @param float $orderAmount Tổng tiền đơn hàng
     * @return array ['success' => bool, 'coupon' => Coupon|null, 'discount' => float, 'message' => string]
     */
    public function validateAndGetCoupon(string $code, float $orderAmount): array
    {
        // Chuẩn hóa mã (viết hoa, trim)
        $code = strtoupper(trim($code));

        // Tìm coupon theo mã
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Mã giảm giá không tồn tại.',
            ];
        }

        // Kiểm tra trạng thái active
        if (!$coupon->is_active) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Mã giảm giá đã bị vô hiệu hóa.',
            ];
        }

        // Kiểm tra thời gian hiệu lực
        $today = now()->toDateString();
        
        if ($coupon->start_date > $today) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Mã giảm giá chưa có hiệu lực. Có hiệu lực từ ' . $coupon->start_date->format('d/m/Y') . '.',
            ];
        }

        if ($coupon->end_date < $today) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Mã giảm giá đã hết hạn vào ngày ' . $coupon->end_date->format('d/m/Y') . '.',
            ];
        }

        // Kiểm tra còn lượt sử dụng
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Mã giảm giá đã hết lượt sử dụng.',
            ];
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($coupon->min_order_amount > 0 && $orderAmount < $coupon->min_order_amount) {
            return [
                'success' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Đơn hàng tối thiểu ' . number_format($coupon->min_order_amount, 0, ',', '.') . 'đ để áp dụng mã này.',
            ];
        }

        // Tính số tiền được giảm
        $discount = $coupon->calculateDiscount($orderAmount);

        // Tạo message thành công
        $discountText = number_format($discount, 0, ',', '.') . 'đ';
        if ($coupon->type === 'percent') {
            $message = "Áp dụng thành công! Giảm {$coupon->value}% (-{$discountText})";
            if ($coupon->max_discount && $discount >= $coupon->max_discount) {
                $message .= " (tối đa " . number_format($coupon->max_discount, 0, ',', '.') . "đ)";
            }
        } else {
            $message = "Áp dụng thành công! Giảm {$discountText}";
        }

        return [
            'success' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => $message,
        ];
    }

    /**
     * Áp dụng coupon cho booking
     * 
     * @param Coupon $coupon
     * @param float $totalAmount Tổng tiền gốc
     * @return array ['discount_amount' => float, 'final_price' => float]
     */
    public function applyCouponToBooking(Coupon $coupon, float $totalAmount): array
    {
        $discount = $coupon->calculateDiscount($totalAmount);
        $finalPrice = $totalAmount - $discount;

        // Tăng số lượt sử dụng
        $coupon->incrementUsage();

        return [
            'discount_amount' => $discount,
            'final_price' => $finalPrice,
        ];
    }

    /**
     * Hoàn lại lượt sử dụng coupon (khi hủy booking)
     * 
     * @param Coupon $coupon
     */
    public function revertCouponUsage(Coupon $coupon): void
    {
        $coupon->decrementUsage();
    }

    /**
     * Tìm kiếm coupon hợp lệ theo code
     * 
     * @param string $code
     * @return Coupon|null
     */
    public function findValidCoupon(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper(trim($code)))
                     ->active()
                     ->valid()
                     ->available()
                     ->first();
    }

    /**
     * Lấy danh sách coupon đang hoạt động (cho admin)
     */
    public function getActiveCoupons()
    {
        return Coupon::active()
                     ->valid()
                     ->available()
                     ->orderBy('end_date', 'asc')
                     ->get();
    }

    /**
     * Tạo coupon mới
     * 
     * @param array $data
     * @return Coupon
     * @throws ValidationException
     */
    public function createCoupon(array $data): Coupon
    {
        // Chuẩn hóa code
        $data['code'] = strtoupper(trim($data['code']));

        // Validate
        $validator = Validator::make($data, [
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.unique' => 'Mã giảm giá này đã tồn tại.',
            'name.required' => 'Vui lòng nhập tên mã giảm giá.',
            'type.required' => 'Vui lòng chọn loại giảm giá.',
            'value.required' => 'Vui lòng nhập giá trị giảm.',
            'value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ]);

        // Validate thêm cho percent type
        $validator->after(function ($validator) use ($data) {
            if ($data['type'] === 'percent' && $data['value'] > 100) {
                $validator->errors()->add('value', 'Phần trăm giảm không được vượt quá 100%.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Coupon::create($data);
    }

    /**
     * Cập nhật coupon
     * 
     * @param Coupon $coupon
     * @param array $data
     * @return Coupon
     * @throws ValidationException
     */
    public function updateCoupon(Coupon $coupon, array $data): Coupon
    {
        // Chuẩn hóa code
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        // Validate
        $validator = Validator::make($data, [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // Validate thêm cho percent type
        $validator->after(function ($validator) use ($data) {
            if ($data['type'] === 'percent' && $data['value'] > 100) {
                $validator->errors()->add('value', 'Phần trăm giảm không được vượt quá 100%.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $coupon->update($data);
        return $coupon->fresh();
    }
}
