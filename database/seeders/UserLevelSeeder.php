<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserLevel;

class UserLevelSeeder extends Seeder
{
    /**
     * Seed 6 level cho hệ thống level người dùng
     */
    public function run(): void
    {
        $levels = [
            [
                'level' => 1,
                'name' => 'Tân binh',
                'icon' => '🌱',
                'frame_color' => 'default',
                'required_tours' => 0,
                'required_reviews' => 0,
                'required_blogs' => 0,
                'discount_percent' => 0,
                'benefits' => json_encode([
                    'Trải nghiệm đặt tour cơ bản',
                ]),
            ],
            [
                'level' => 2,
                'name' => 'Khám phá',
                'icon' => '🥾',
                'frame_color' => 'silver',
                'required_tours' => 2,
                'required_reviews' => 2,
                'required_blogs' => 0,
                'discount_percent' => 3,
                'benefits' => json_encode([
                    'Giảm 3% mỗi booking',
                    'Khung avatar bạc',
                ]),
            ],
            [
                'level' => 3,
                'name' => 'Nhà leo núi',
                'icon' => '⛰️',
                'frame_color' => 'green',
                'required_tours' => 5,
                'required_reviews' => 5,
                'required_blogs' => 2,
                'discount_percent' => 5,
                'benefits' => json_encode([
                    'Giảm 5% mỗi booking',
                    'Khung avatar xanh lục',
                ]),
            ],
            [
                'level' => 4,
                'name' => 'Chinh phục gia',
                'icon' => '🏔️',
                'frame_color' => 'gold',
                'required_tours' => 10,
                'required_reviews' => 8,
                'required_blogs' => 5,
                'discount_percent' => 8,
                'benefits' => json_encode([
                    'Giảm 8% mỗi booking',
                    'Khung avatar vàng',
                    'Mã giảm giá ưu đãi riêng',
                ]),
            ],
            [
                'level' => 5,
                'name' => 'Dũng sĩ',
                'icon' => '🦅',
                'frame_color' => 'diamond',
                'required_tours' => 18,
                'required_reviews' => 15,
                'required_blogs' => 8,
                'discount_percent' => 12,
                'benefits' => json_encode([
                    'Giảm 12% mỗi booking',
                    'Khung avatar kim cương',
                    'Mã giảm giá ưu đãi riêng',
                ]),
            ],
            [
                'level' => 6,
                'name' => 'Huyền thoại',
                'icon' => '👑',
                'frame_color' => 'legendary',
                'required_tours' => 30,
                'required_reviews' => 25,
                'required_blogs' => 15,
                'discount_percent' => 15,
                'benefits' => json_encode([
                    'Giảm 15% mỗi booking',
                    'Khung avatar huyền thoại',
                    'Mã giảm giá ưu đãi riêng',
                ]),
            ],
        ];

        foreach ($levels as $level) {
            UserLevel::updateOrCreate(
                ['level' => $level['level']],
                $level
            );
        }
    }
}
