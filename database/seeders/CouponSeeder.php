<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho mã giảm giá
     */
    public function run(): void
    {
        // Lấy super_admin để gán created_by
        $superAdmin = User::where('role', 'super_admin')->first();
        $createdBy = $superAdmin ? $superAdmin->id : null;

        // Mã giảm theo phần trăm
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Chào mừng thành viên mới',
            'description' => 'Giảm 10% cho đơn hàng đầu tiên của thành viên mới',
            'type' => 'percent',
            'value' => 10,
            'min_order_amount' => 500000,
            'max_discount' => 200000,
            'usage_limit' => 100,
            'used_count' => 12,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(60),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Mã giảm cố định
        Coupon::create([
            'code' => 'SAVE100K',
            'name' => 'Giảm 100K cho đơn từ 1 triệu',
            'description' => 'Áp dụng cho tất cả các tour có giá trị từ 1 triệu đồng',
            'type' => 'fixed',
            'value' => 100000,
            'min_order_amount' => 1000000,
            'max_discount' => null,
            'usage_limit' => 50,
            'used_count' => 8,
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(45),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Mã giảm lớn - có giới hạn max
        Coupon::create([
            'code' => 'SUMMER25',
            'name' => 'Khuyến mãi hè 2026',
            'description' => 'Giảm 25% tối đa 500K cho các tour mùa hè',
            'type' => 'percent',
            'value' => 25,
            'min_order_amount' => 2000000,
            'max_discount' => 500000,
            'usage_limit' => 200,
            'used_count' => 0,
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(120),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Mã không giới hạn lượt sử dụng
        Coupon::create([
            'code' => 'MOUNTAIN15',
            'name' => 'Ưu đãi thường xuyên',
            'description' => 'Giảm 15% cho tất cả tour leo núi',
            'type' => 'percent',
            'value' => 15,
            'min_order_amount' => 0,
            'max_discount' => 300000,
            'usage_limit' => null, // Không giới hạn
            'used_count' => 45,
            'start_date' => now()->subDays(60),
            'end_date' => now()->addDays(300),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Mã đã hết hạn (để test)
        Coupon::create([
            'code' => 'NEWYEAR24',
            'name' => 'Khuyến mãi Tết 2024',
            'description' => 'Đã hết hạn',
            'type' => 'percent',
            'value' => 20,
            'min_order_amount' => 500000,
            'max_discount' => 400000,
            'usage_limit' => 100,
            'used_count' => 87,
            'start_date' => now()->subDays(400),
            'end_date' => now()->subDays(350),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        // Mã đã tắt
        Coupon::create([
            'code' => 'FLASH50',
            'name' => 'Flash Sale 50%',
            'description' => 'Đã tạm dừng',
            'type' => 'percent',
            'value' => 50,
            'min_order_amount' => 3000000,
            'max_discount' => 1000000,
            'usage_limit' => 10,
            'used_count' => 10,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
            'is_active' => false,
            'created_by' => $createdBy,
        ]);
    }
}
