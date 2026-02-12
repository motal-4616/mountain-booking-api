<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\Booking;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckScheduleMinimumParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-schedule-minimum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và hủy các lịch trình không đủ số lượng người tối thiểu tại registration deadline';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info("Kiểm tra các lịch trình đến thời hạn đăng ký...");
        
        $now = now();
        
        // Lấy các schedule:
        // - Đang active
        // - Chưa khởi hành
        // - Đã đến hoặc qua registration deadline
        $schedules = Schedule::where('is_active', true)
            ->where('departure_date', '>', $now)
            ->with(['tour', 'bookings' => function($query) {
                $query->whereIn('status', ['pending', 'confirmed']);
            }])
            ->get()
            ->filter(function($schedule) use ($now) {
                // Chỉ lấy schedules đã đến registration deadline
                return $schedule->registration_deadline <= $now;
            });
        
        $cancelledCount = 0;
        
        foreach ($schedules as $schedule) {
            $minPeople = $schedule->min_people;
            $bookedPeople = $schedule->booked_people;
            
            $this->line("Schedule #{$schedule->id} - {$schedule->tour->name}");
            $this->line("  Ngày khởi hành: {$schedule->formatted_date}");
            $this->line("  Deadline đăng ký: {$schedule->registration_deadline->format('d/m/Y H:i')}");
            $this->line("  Đã đặt: {$bookedPeople}/{$minPeople} người");
            
            // Nếu không có người đăng ký hoặc không đủ số người tối thiểu
            if ($bookedPeople == 0) {
                $this->warn("  ⚠️  Không có người đăng ký! Đang hủy...");
                
                $schedule->is_active = false;
                $schedule->save();
                
                // Thông báo cho admin
                $this->notifyAdminsNoRegistration($schedule, $notificationService);
                
                $cancelledCount++;
                $this->info("  ✓ Đã hủy schedule (không có đăng ký)");
            } 
            elseif ($bookedPeople < $minPeople) {
                $this->warn("  ⚠️  Không đủ số người tối thiểu! Đang hủy...");
                
                // Hủy schedule
                $schedule->is_active = false;
                $schedule->save();
                
                // Lấy tất cả bookings chưa hủy
                $activeBookings = $schedule->bookings()
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->with('user')
                    ->get();
                
                // Hủy từng booking và thông báo
                foreach ($activeBookings as $booking) {
                    $this->cancelBookingAndNotify($booking, $schedule, $minPeople, $bookedPeople, $notificationService);
                }
                
                // Thông báo cho admin
                $this->notifyAdmins($schedule, $minPeople, $bookedPeople, $notificationService);
                
                $cancelledCount++;
                $this->info("  ✓ Đã hủy schedule và {$activeBookings->count()} bookings");
            } else {
                $this->info("  ✓ Đủ số người - Tour được xác nhận");
            }
        }
        
        $this->info("\n✅ Hoàn tất! Đã kiểm tra {$schedules->count()} lịch trình, hủy {$cancelledCount} lịch trình.");
        
        return Command::SUCCESS;
    }

    /**
     * Hủy booking và gửi thông báo cho user
     */
    private function cancelBookingAndNotify(Booking $booking, Schedule $schedule, int $minPeople, int $bookedPeople, NotificationService $notificationService)
    {
        // Cập nhật trạng thái booking
        $booking->status = 'cancelled';
        $booking->cancellation_reason = "Tour không đủ số lượng người tối thiểu ({$bookedPeople}/{$minPeople} người). Chúng tôi rất tiếc về sự bất tiện này.";
        $booking->cancelled_at = now();
        $booking->save();
        
        // Hoàn slots
        $schedule->available_slots += $booking->quantity;
        $schedule->save();
        
        // Gửi thông báo cho user
        if ($booking->user) {
            $notificationService->sendDatabaseNotification(
                $booking->user,
                'App\\Notifications\\BookingCancelledNotification',
                [
                    'title' => 'Hủy tour do không đủ số lượng',
                    'message' => "Tour \"{$schedule->tour->name}\" ngày {$schedule->formatted_date} đã bị hủy do không đủ số lượng người tối thiểu ({$bookedPeople}/{$minPeople}). Số tiền " . number_format($booking->final_price) . "đ sẽ được hoàn lại theo chính sách. Chúng tôi xin lỗi vì sự bất tiện này.",
                    'icon' => 'bi-exclamation-triangle',
                    'color' => 'warning',
                    'url' => route('bookings.show', $booking),
                    'booking_id' => $booking->id,
                ]
            );
        }
    }

    /**
     * Thông báo cho admins - không có người đăng ký
     */
    private function notifyAdminsNoRegistration(Schedule $schedule, NotificationService $notificationService)
    {
        $admins = User::whereIn('role', ['super_admin', 'booking_manager'])->get();
        
        foreach ($admins as $admin) {
            $notificationService->sendDatabaseNotification(
                $admin,
                'App\\Notifications\\SystemAlertNotification',
                [
                    'title' => 'Lịch trình bị hủy - Không có đăng ký',
                    'message' => "Lịch trình \"{$schedule->tour->name}\" ngày {$schedule->formatted_date} đã tự động bị hủy do không có người đăng ký đến thời hạn đăng ký ({$schedule->registration_deadline->format('d/m/Y H:i')}).",
                    'icon' => 'bi-calendar-x',
                    'color' => 'danger',
                    'url' => route('admin.schedules.show', $schedule),
                    'schedule_id' => $schedule->id,
                ]
            );
        }
    }

    /**
     * Thông báo cho admins - không đủ người
     */
    private function notifyAdmins(Schedule $schedule, int $minPeople, int $bookedPeople, NotificationService $notificationService)
    {
        $admins = User::whereIn('role', ['super_admin', 'booking_manager'])->get();
        
        foreach ($admins as $admin) {
            $notificationService->sendDatabaseNotification(
                $admin,
                'App\\Notifications\\SystemAlertNotification',
                [
                    'title' => 'Lịch trình bị hủy do không đủ người',
                    'message' => "Lịch trình \"{$schedule->tour->name}\" ngày {$schedule->formatted_date} đã tự động bị hủy do không đủ số lượng người tối thiểu ({$bookedPeople}/{$minPeople}). Tất cả bookings đã được hủy và thông báo đến khách hàng.",
                    'icon' => 'bi-calendar-x',
                    'color' => 'danger',
                    'url' => route('admin.schedules.show', $schedule),
                    'schedule_id' => $schedule->id,
                ]
            );
        }
    }
}
