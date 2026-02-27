<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Notifications\TourReminderNotification;
use Carbon\Carbon;

class SendTourReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Gửi nhắc nhở cho user trước ngày khởi hành tour (1 ngày và 3 ngày)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra booking cần gửi nhắc nhở...');

        $today = Carbon::today();
        $sentCount = 0;

        // Gửi nhắc nhở cho 1 ngày và 3 ngày trước khởi hành
        foreach ([1, 3] as $daysUntil) {
            $targetDate = $today->copy()->addDays($daysUntil)->toDateString();

            $bookings = Booking::where('status', 'confirmed')
                ->whereHas('schedule', function ($query) use ($targetDate) {
                    $query->where('departure_date', $targetDate);
                })
                ->with(['schedule.tour', 'user'])
                ->get();

            foreach ($bookings as $booking) {
                if (!$booking->user) {
                    continue;
                }

                // Kiểm tra đã gửi nhắc nhở cho ngày này chưa (tránh gửi trùng)
                $alreadySent = $booking->user->notifications()
                    ->where('type', TourReminderNotification::class)
                    ->whereDate('created_at', $today)
                    ->whereJsonContains('data->booking_id', $booking->id)
                    ->whereJsonContains('data->days_until', $daysUntil)
                    ->exists();

                if ($alreadySent) {
                    $this->line("  - Đã gửi nhắc cho booking #{$booking->id} ({$daysUntil} ngày), bỏ qua.");
                    continue;
                }

                try {
                    $booking->user->notify(new TourReminderNotification($booking, $daysUntil));
                    $sentCount++;
                    $this->line("  ✓ Đã gửi nhắc {$daysUntil} ngày cho booking #{$booking->id} - {$booking->user->name}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Lỗi gửi nhắc booking #{$booking->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Hoàn tất! Đã gửi {$sentCount} nhắc nhở.");
        return Command::SUCCESS;
    }
}
