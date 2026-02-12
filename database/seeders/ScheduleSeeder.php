<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Tour;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả tours
        $tours = Tour::all();

        if ($tours->isEmpty()) {
            $this->command->warn('Không có tour nào. Vui lòng chạy TourSeeder trước!');
            return;
        }

        $schedules = [];

        foreach ($tours as $tour) {
            // Tạo 8-12 lịch trình cho mỗi tour trong vòng 3 tháng tới
            $scheduleCount = rand(8, 12);
            $usedDates = []; // Theo dõi ngày đã dùng để tránh trùng
            
            $attempts = 0;
            $created = 0;
            
            while ($created < $scheduleCount && $attempts < 50) {
                $attempts++;
                
                // Tạo ngày khởi hành ngẫu nhiên trong 3 tháng tới
                if ($created % 3 === 0) {
                    // Ưu tiên cuối tuần
                    $departureDate = $this->getNextWeekend()->addWeeks(rand(0, 12));
                } else {
                    $departureDate = Carbon::now()->addDays(rand(5, 90));
                }
                
                $dateStr = $departureDate->format('Y-m-d');
                
                // Kiểm tra trùng ngày
                if (in_array($dateStr, $usedDates)) {
                    continue;
                }
                
                $usedDates[] = $dateStr;
                
                // Tính ngày kết thúc (tour 1-5 ngày)
                $durationDays = rand(1, 5);
                $endDate = $durationDays > 1 ? $departureDate->copy()->addDays($durationDays - 1) : null;
                
                // Số chỗ tối đa
                $maxPeople = rand(10, 25);
                
                // Số chỗ còn lại (luôn còn ít nhất 3 chỗ)
                $booked = rand(0, min(5, $maxPeople - 3)); // Đã đặt tối đa 5 người
                $availableSlots = $maxPeople - $booked;
                
                // Trạng thái
                $isActive = true;
                
                // Nếu quá gần hoặc đã qua, không hoạt động
                if ($departureDate->diffInDays(Carbon::now()) <= 2 || $departureDate < Carbon::now()) {
                    $isActive = false;
                }
                
                $schedules[] = [
                    'tour_id' => $tour->id,
                    'departure_date' => $dateStr,
                    'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                    'max_people' => $maxPeople,
                    'available_slots' => $availableSlots,
                    'is_active' => $isActive,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $created++;
            }
        }

        // Insert tất cả schedules
        Schedule::insert($schedules);
        
        $this->command->info('Đã tạo ' . count($schedules) . ' lịch trình cho ' . $tours->count() . ' tour!');
    }

    /**
     * Lấy thứ 7 tiếp theo
     */
    private function getNextWeekend(): Carbon
    {
        $date = Carbon::now();
        while ($date->dayOfWeek !== Carbon::SATURDAY) {
            $date->addDay();
        }
        return $date;
    }
}
