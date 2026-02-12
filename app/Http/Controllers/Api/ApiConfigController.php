<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class ApiConfigController extends ApiController
{
    /**
     * Get app configuration
     */
    public function index(Request $request)
    {
        $config = [
            'app_name' => config('app.name'),
            'app_version' => '1.0.0',
            'payment_methods' => [
                [
                    'code' => 'vnpay',
                    'name' => 'VNPay',
                    'description' => 'Thanh toán qua VNPay (ATM, VISA, MasterCard)',
                    'enabled' => true,
                    'icon' => asset('images/vnpay-logo.png'),
                ],
                [
                    'code' => 'cash',
                    'name' => 'Tiền mặt',
                    'description' => 'Thanh toán tiền mặt khi khởi hành',
                    'enabled' => true,
                    'icon' => asset('images/cash-icon.png'),
                ],
            ],
            'difficulty_levels' => [
                ['code' => 'easy', 'name' => 'Dễ', 'color' => '#10B981'],
                ['code' => 'medium', 'name' => 'Trung bình', 'color' => '#F59E0B'],
                ['code' => 'hard', 'name' => 'Khó', 'color' => '#EF4444'],
            ],
            'booking_statuses' => [
                ['code' => 'pending', 'name' => 'Chờ xác nhận', 'color' => '#F59E0B'],
                ['code' => 'confirmed', 'name' => 'Đã xác nhận', 'color' => '#3B82F6'],
                ['code' => 'completed', 'name' => 'Hoàn thành', 'color' => '#10B981'],
                ['code' => 'cancelled', 'name' => 'Đã hủy', 'color' => '#EF4444'],
                ['code' => 'refunded', 'name' => 'Đã hoàn tiền', 'color' => '#6B7280'],
            ],
            'contact_info' => [
                'phone' => '024 1234 5678',
                'email' => 'info@mountainbooking.vn',
                'address' => '123 Đường ABC, Quận 1, TP.HCM',
                'facebook' => 'https://facebook.com/mountainbooking',
                'instagram' => 'https://instagram.com/mountainbooking',
                'zalo' => '0912345678',
            ],
            'policies' => [
                'cancellation_policy' => 'Bạn có thể hủy booking trước 7 ngày để được hoàn tiền 100%',
                'privacy_policy_url' => route('home') . '/privacy-policy',
                'terms_url' => route('home') . '/terms-conditions',
            ],
        ];

        return $this->successResponse($config, 'Lấy cấu hình thành công');
    }
}
