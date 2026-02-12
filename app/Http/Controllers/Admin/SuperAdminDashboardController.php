<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\User;
use App\Models\Schedule;
use App\Models\BookingPayment;
use App\Models\Review;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard cho Super Admin - Tổng quan toàn bộ hệ thống
 */
class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        // Thống kê tổng quan toàn hệ thống
        $stats = [
            // Người dùng
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::whereIn('role', ['super_admin', 'booking_manager', 'content_manager'])->count(),
            'new_users_this_month' => User::where('role', 'user')
                ->whereMonth('created_at', now()->month)
                ->count(),
            
            // Tours & Schedules
            'total_tours' => Tour::count(),
            'active_tours' => Tour::where('is_active', true)->count(),
            'total_schedules' => Schedule::count(),
            'upcoming_schedules' => Schedule::where('departure_date', '>=', now())
                ->where('is_active', true)
                ->count(),
            
            // Bookings
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::whereIn('status', ['cancelled', 'refunded'])->count(),
            
            // Doanh thu
            'total_revenue' => BookingPayment::where('status', 'success')->sum('amount'),
            'revenue_this_month' => BookingPayment::where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'revenue_this_year' => BookingPayment::where('status', 'success')
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            
            // Reviews & Coupons
            'total_reviews' => Review::count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'avg_rating' => round(Review::where('status', 'approved')->avg('rating'), 1),
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
        ];

        // Biểu đồ doanh thu 12 tháng
        $revenueChart = $this->getRevenueChart();
        
        // Top tours theo doanh thu
        $topTours = $this->getTopToursByRevenue();
        
        // Thống kê user theo role
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get()
            ->pluck('total', 'role');
        
        // Đơn đặt vé mới nhất
        $recentBookings = Booking::with(['user', 'schedule.tour', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Thống kê booking theo trạng thái
        $bookingsByStatus = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        return view('admin.dashboards.superadmin', compact(
            'stats',
            'revenueChart',
            'topTours',
            'usersByRole',
            'recentBookings',
            'bookingsByStatus'
        ));
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu 12 tháng
     */
    private function getRevenueChart()
    {
        $months = [];
        $revenues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $revenue = BookingPayment::where('status', 'success')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            
            $revenues[] = $revenue;
        }
        
        return [
            'labels' => $months,
            'data' => $revenues
        ];
    }

    /**
     * Top 5 tours có doanh thu cao nhất
     */
    private function getTopToursByRevenue()
    {
        return Tour::select(
                'tours.id',
                'tours.name',
                'tours.location',
                'tours.duration_days',
                DB::raw('SUM(booking_payments.amount) as total_revenue')
            )
            ->join('schedules', 'tours.id', '=', 'schedules.tour_id')
            ->join('bookings', 'schedules.id', '=', 'bookings.schedule_id')
            ->join('booking_payments', 'bookings.id', '=', 'booking_payments.booking_id')
            ->where('booking_payments.status', 'success')
            ->groupBy('tours.id', 'tours.name', 'tours.location', 'tours.duration_days')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }
}
