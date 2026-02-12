<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard cho Booking Manager - Tập trung vào quản lý booking
 */
class BookingManagerDashboardController extends Controller
{
    public function index()
    {
        // Thống kê về booking
        $stats = [
            // Booking overview
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::whereIn('status', ['cancelled', 'refunded'])->count(),
            
            // Bookings hôm nay
            'today_bookings' => Booking::whereDate('created_at', today())->count(),
            'today_pending' => Booking::whereDate('created_at', today())
                ->where('status', 'pending')
                ->count(),
            
            // Doanh thu từ booking
            'total_revenue' => BookingPayment::where('status', 'success')->sum('amount'),
            'revenue_this_month' => BookingPayment::where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'pending_payments' => BookingPayment::where('status', 'pending')->count(),
            
            // Lịch trình sắp khởi hành (trong 7 ngày)
            'upcoming_schedules' => Schedule::where('departure_date', '>=', now())
                ->where('departure_date', '<=', now()->addDays(7))
                ->where('is_active', true)
                ->count(),
        ];

        // Bookings cần xử lý (pending)
        $pendingBookings = Booking::with(['user', 'schedule.tour', 'payments'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Bookings mới nhất
        $recentBookings = Booking::with(['user', 'schedule.tour', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Lịch trình sắp khởi hành cần theo dõi
        $upcomingSchedules = Schedule::with(['tour', 'bookings'])
            ->where('departure_date', '>=', now())
            ->where('departure_date', '<=', now()->addDays(7))
            ->where('is_active', true)
            ->orderBy('departure_date')
            ->get();

        // Biểu đồ booking 7 ngày qua
        $bookingChart = $this->getBookingChart();
        
        // Thống kê booking theo trạng thái
        $bookingsByStatus = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        // Thanh toán cần xác nhận
        $pendingPayments = BookingPayment::with('booking.schedule.tour')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboards.booking-manager', compact(
            'stats',
            'pendingBookings',
            'recentBookings',
            'upcomingSchedules',
            'bookingChart',
            'bookingsByStatus',
            'pendingPayments'
        ));
    }

    /**
     * Biểu đồ booking 7 ngày gần đây
     */
    private function getBookingChart()
    {
        $days = [];
        $bookings = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('d/m');
            
            $count = Booking::whereDate('created_at', $date)->count();
            $bookings[] = $count;
        }
        
        return [
            'labels' => $days,
            'data' => $bookings
        ];
    }
}
