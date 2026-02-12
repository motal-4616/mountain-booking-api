<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tour;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho database
     */
    public function run(): void
    {
        // ===== TẠO TÀI KHOẢN SUPER ADMIN =====
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'phone' => '0900000000',
            'password' => Hash::make('123456'),
            'role' => 'super_admin',
        ]);

        // ===== TẠO TÀI KHOẢN ADMIN =====
        User::create([
            'name' => 'Content Manager',
            'email' => 'content@gmail.com',
            'phone' => '0901234567',
            'password' => Hash::make('123456'),
            'role' => 'content_manager',
        ]);

        // ===== TẠO TÀI KHOẢN BOOKING MANAGER =====
        User::create([
            'name' => 'Booking Manager',
            'email' => 'booking@gmail.com',
            'phone' => '0902345678',
            'password' => Hash::make('123456'),
            'role' => 'booking_manager',
        ]);

        // ===== TẠO TÀI KHOẢN USER MẪU =====
        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'user@gmail.com',
            'phone' => '0987654321',
            'password' => Hash::make('123456'),
            'role' => 'user',
        ]);

        // ===== TẠO DỮ LIỆU TOURS, SCHEDULES VÀ COUPONS =====
        $this->call([
            TourSeeder::class,
            ScheduleSeeder::class,
            CouponSeeder::class,
            UserAndBlogSeeder::class,
        ]);
    }
}
