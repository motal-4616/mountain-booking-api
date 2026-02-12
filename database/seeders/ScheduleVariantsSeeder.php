<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleVariantsSeeder extends Seeder
{
    /**
     * Seed schedules với nhiều variants (1 day, 2 days, 3 days, 5 days)
     * để test tính năng hiển thị lịch trình động
     */
    public function run(): void
    {
        $this->command->info("🗑️  Xóa schedules cũ (giữ lại schedules có bookings)...");
        
        // Chỉ xóa schedules chưa có booking
        $deletedCount = Schedule::whereDoesntHave('bookings')->delete();
        $this->command->info("   Đã xóa {$deletedCount} schedules");
        
        // Lấy tour Fansipan (có lịch trình 3 ngày chuẩn)
        $fansipan = Tour::where('name', 'LIKE', '%Fansipan%')->first();
        
        if ($fansipan) {
            $baseDate = Carbon::now()->addDays(10);
            
            // Giá cơ bản của tour Fansipan
            $basePrice = $fansipan->price;
            
            // Thêm schedules với duration khác nhau và giá khác nhau
            $variants = [
                // Tour 1 ngày - express (giảm giá 30%)
                [
                    'departure_date' => $baseDate->copy()->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->format('Y-m-d'),
                    'max_people' => 15,
                    'available_slots' => 12,
                    'price' => $basePrice * 0.7, // Giảm 30%
                ],
                // Tour 2 ngày - cơ bản (giảm giá 10%)
                [
                    'departure_date' => $baseDate->copy()->addDays(3)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(4)->format('Y-m-d'),
                    'max_people' => 12,
                    'available_slots' => 10,
                    'price' => $basePrice * 0.9, // Giảm 10%
                ],
                // Tour 3 ngày - chuẩn (giá gốc)
                [
                    'departure_date' => $baseDate->copy()->addDays(7)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(9)->format('Y-m-d'),
                    'max_people' => 15,
                    'available_slots' => 15,
                    'price' => $basePrice, // Giá chuẩn
                ],
                [
                    'departure_date' => $baseDate->copy()->addDays(14)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(16)->format('Y-m-d'),
                    'max_people' => 15,
                    'available_slots' => 15,
                    'price' => $basePrice, // Giá chuẩn
                ],
                // Tour 5 ngày - mở rộng (tăng giá 30%)
                [
                    'departure_date' => $baseDate->copy()->addDays(20)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(24)->format('Y-m-d'),
                    'max_people' => 10,
                    'available_slots' => 8,
                    'price' => $basePrice * 1.3, // Tăng 30%
                ],
                [
                    'departure_date' => $baseDate->copy()->addDays(28)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(32)->format('Y-m-d'),
                    'max_people' => 10,
                    'available_slots' => 8,
                    'price' => $basePrice * 1.3, // Tăng 30%
                ],
            ];

            foreach ($variants as $variant) {
                Schedule::create([
                    'tour_id' => $fansipan->id,
                    'departure_date' => $variant['departure_date'],
                    'end_date' => $variant['end_date'],
                    'max_people' => $variant['max_people'],
                    'available_slots' => $variant['available_slots'],
                    'price' => $variant['price'],
                ]);
            }
            
            $this->command->info("✓ Đã thêm 6 schedules variants cho {$fansipan->name} với giá khác nhau");
        }
        
        // Lấy tour Tà Chì Nhù
        $tachiNhu = Tour::where('name', 'LIKE', '%Tà Chì Nhù%')->first();
        
        if ($tachiNhu) {
            $baseDate = Carbon::now()->addDays(5);
            $basePrice = $tachiNhu->price;
            
            $variants = [
                // Tour 1 ngày (giảm giá 40%)
                [
                    'departure_date' => $baseDate->copy()->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->format('Y-m-d'),
                    'max_people' => 12,
                    'available_slots' => 10,
                    'price' => $basePrice * 0.6,
                ],
                // Tour 3 ngày - chuẩn (giá gốc)
                [
                    'departure_date' => $baseDate->copy()->addDays(5)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(7)->format('Y-m-d'),
                    'max_people' => 15,
                    'available_slots' => 12,
                    'price' => $basePrice,
                ],
                [
                    'departure_date' => $baseDate->copy()->addDays(12)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(14)->format('Y-m-d'),
                    'max_people' => 15,
                    'available_slots' => 12,
                    'price' => $basePrice,
                ],
                // Tour 4 ngày - mở rộng nhẹ (tăng giá 20%)
                [
                    'departure_date' => $baseDate->copy()->addDays(18)->format('Y-m-d'),
                    'end_date' => $baseDate->copy()->addDays(21)->format('Y-m-d'),
                    'max_people' => 12,
                    'available_slots' => 10,
                    'price' => $basePrice * 1.2,
                ],
            ];

            foreach ($variants as $variant) {
                Schedule::create([
                    'tour_id' => $tachiNhu->id,
                    'departure_date' => $variant['departure_date'],
                    'end_date' => $variant['end_date'],
                    'max_people' => $variant['max_people'],
                    'available_slots' => $variant['available_slots'],
                    'price' => $variant['price'],
                ]);
            }
            
            $this->command->info("✓ Đã thêm 4 schedules variants cho {$tachiNhu->name} với giá khác nhau");
        }
        
        // Lấy tour Núi Chứa Chan (tour 1 ngày)
        $chuaChan = Tour::where('name', 'LIKE', '%Chứa Chan%')->first();
        
        if ($chuaChan) {
            $baseDate = Carbon::now()->addDays(3);
            $basePrice = $chuaChan->price;
            
            // Chỉ thêm tour 1 ngày (đúng với tour này) với giá giống nhau
            $dates = [
                $baseDate->copy()->format('Y-m-d'),
                $baseDate->copy()->addDays(7)->format('Y-m-d'),
                $baseDate->copy()->addDays(14)->format('Y-m-d'),
                $baseDate->copy()->addDays(21)->format('Y-m-d'),
            ];

            foreach ($dates as $date) {
                Schedule::create([
                    'tour_id' => $chuaChan->id,
                    'departure_date' => $date,
                    'end_date' => $date,
                    'max_people' => 25,
                    'available_slots' => 20,
                    'price' => $basePrice, // Giá cố định
                ]);
            }
            
            $this->command->info("✓ Đã thêm 4 schedules (1 ngày) cho {$chuaChan->name}");
        }
        
        $this->command->info("✅ Hoàn tất! Database đã có schedules với nhiều duration variants và giá khác nhau.");
        $this->command->warn("💡 Tip: Truy cập trang tour để xem các variants hiển thị với giá riêng biệt!");
    }
}
