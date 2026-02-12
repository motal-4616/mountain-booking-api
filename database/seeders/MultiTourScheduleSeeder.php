<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\Schedule;
use Carbon\Carbon;

class MultiTourScheduleSeeder extends Seeder
{
    /**
     * Thêm lịch trình cho tất cả các tour
     */
    public function run(): void
    {
        // Lấy tất cả tour
        $tours = Tour::all();
        
        if ($tours->isEmpty()) {
            $this->command->warn('⚠️ Không có tour nào trong database!');
            return;
        }

        $this->command->info("🎯 Bắt đầu tạo lịch trình cho {$tours->count()} tour...\n");

        foreach ($tours as $tour) {
            // Lấy thời lượng tour (từ duration hoặc tính từ itinerary)
            $baseDuration = $this->getTourDuration($tour);
            
            // Tạo các variant duration
            $variants = [
                [
                    'duration' => max(1, $baseDuration - 2),
                    'label' => 'Nhanh',
                    'priceMultiplier' => 0.70
                ],
                [
                    'duration' => $baseDuration,
                    'label' => 'Chuẩn',
                    'priceMultiplier' => 1.0
                ],
                [
                    'duration' => $baseDuration + 1,
                    'label' => 'Thoải mái',
                    'priceMultiplier' => 1.15
                ],
                [
                    'duration' => $baseDuration + 2,
                    'label' => 'Trọn gói',
                    'priceMultiplier' => 1.30
                ]
            ];

            // Giá cơ bản dựa trên độ khó
            $basePrice = match($tour->difficulty) {
                'easy' => 2000000,
                'medium' => 3000000,
                'hard' => 4500000,
                default => 2500000
            };

            $this->command->info("📍 {$tour->name}");
            $createdCount = 0;

            // Tạo 3-4 lịch trình cho mỗi tour
            for ($i = 0; $i < 4; $i++) {
                $variant = $variants[$i];
                $duration = $variant['duration'];
                
                // Ngày khởi hành (từ 7 ngày sau đến 90 ngày sau)
                $daysFromNow = rand(7, 90);
                $departureDate = Carbon::now()->addDays($daysFromNow);
                $endDate = $departureDate->copy()->addDays($duration - 1);

                // Tính giá
                $price = round($basePrice * $variant['priceMultiplier'] / 10000) * 10000;

                // Số người tối đa
                $maxPeople = rand(8, 20);
                $availableSlots = rand(floor($maxPeople * 0.3), $maxPeople);

                Schedule::create([
                    'tour_id' => $tour->id,
                    'departure_date' => $departureDate,
                    'end_date' => $endDate,
                    'max_people' => $maxPeople,
                    'available_slots' => $availableSlots,
                    'price' => $price,
                    'is_active' => true
                ]);

                $createdCount++;
                $this->command->line("   ✅ {$departureDate->format('d/m/Y')} - {$endDate->format('d/m/Y')} ({$duration}N): " . number_format($price) . "₫");
            }

            $this->command->info("   📊 Đã tạo {$createdCount} lịch trình\n");
        }

        $totalSchedules = Schedule::count();
        $this->command->info("🎉 Hoàn thành! Tổng cộng: {$totalSchedules} lịch trình");
    }

    /**
     * Lấy thời lượng tour
     */
    private function getTourDuration($tour): int
    {
        // Kiểm tra nếu có duration field
        if (isset($tour->duration) && $tour->duration > 0) {
            return $tour->duration;
        }

        // Tính từ itinerary
        if ($tour->itinerary && is_array($tour->itinerary)) {
            return count($tour->itinerary);
        }

        // Mặc định dựa trên độ khó
        return match($tour->difficulty) {
            'easy' => 2,
            'medium' => 3,
            'hard' => 5,
            default => 3
        };
    }
}
