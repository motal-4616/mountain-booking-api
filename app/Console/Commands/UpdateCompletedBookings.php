<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class UpdateCompletedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động cập nhật trạng thái booking thành hoàn thành khi tour đã kết thúc';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra các booking đã hoàn thành...');

        // Lấy các booking đã xác nhận và tour đã kết thúc
        $bookings = Booking::where('status', 'confirmed')
            ->whereHas('schedule', function($query) {
                $query->where(function($q) {
                    // Nếu có end_date thì check end_date, không thì check departure_date
                    $q->where('end_date', '<', Carbon::now())
                      ->orWhere(function($q2) {
                          $q2->whereNull('end_date')
                             ->where('departure_date', '<', Carbon::now()->subDays(3)); // Giả sử tour dài 3 ngày
                      });
                });
            })
            ->get()
            ->filter(fn($booking) => $booking->payment_status === 'paid');

        $count = 0;
        foreach ($bookings as $booking) {
            $booking->update(['status' => 'completed']);
            $count++;
            $this->line("✓ Đã cập nhật booking #{$booking->id}");
        }

        $this->info("Hoàn thành! Đã cập nhật {$count} booking.");
        
        return Command::SUCCESS;
    }
}
