<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Schedule;
use App\Models\Tour;

return new class extends Migration
{
    /**
     * Cập nhật giá cho các schedules hiện có dựa trên tour và duration
     */
    public function up(): void
    {
        // Lấy tất cả schedules
        $schedules = Schedule::with('tour')->get();
        
        foreach ($schedules as $schedule) {
            if (!$schedule->tour) {
                continue;
            }
            
            $tour = $schedule->tour;
            $basePrice = $tour->price;
            
            // Tính duration của schedule
            $days = $schedule->end_date 
                ? \Carbon\Carbon::parse($schedule->departure_date)->diffInDays(\Carbon\Carbon::parse($schedule->end_date)) + 1
                : 1;
            
            // Tính giá dựa trên duration so với tour chuẩn
            $tourDuration = $tour->duration_days;
            
            if ($days < $tourDuration) {
                // Tour ngắn hơn chuẩn -> giảm giá theo tỷ lệ
                $discount = ($tourDuration - $days) * 0.15; // Giảm 15% mỗi ngày
                $price = $basePrice * (1 - min($discount, 0.4)); // Tối đa giảm 40%
            } elseif ($days > $tourDuration) {
                // Tour dài hơn chuẩn -> tăng giá theo tỷ lệ
                $increase = ($days - $tourDuration) * 0.15; // Tăng 15% mỗi ngày
                $price = $basePrice * (1 + min($increase, 0.5)); // Tối đa tăng 50%
            } else {
                // Tour chuẩn -> giá gốc
                $price = $basePrice;
            }
            
            // Làm tròn giá về bội số 10,000
            $price = round($price / 10000) * 10000;
            
            // Cập nhật giá cho schedule
            $schedule->update(['price' => $price]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set tất cả giá về 0
        Schedule::query()->update(['price' => 0]);
    }
};
