<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controller báo cáo doanh thu
 */
class RevenueController extends Controller
{
    /**
     * Báo cáo doanh thu tổng quan
     */
    public function index(Request $request)
    {
        $reportType = $request->get('type', 'month'); // day, month, year
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Thống kê tổng quan
        $totalStats = [
            'total_bookings' => Booking::count(),
            'total_revenue' => BookingPayment::where('status', 'success')->sum('amount'),
            'pending_revenue' => BookingPayment::where('status', 'pending')->sum('amount'),
            'total_amount' => Booking::sum('total_amount'),
        ];

        // Báo cáo theo loại
        $revenueData = [];
        
        if ($reportType === 'day') {
            // Báo cáo theo ngày trong tháng
            $revenueData = BookingPayment::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'success')
                ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT booking_id) as total_bookings, SUM(amount) as revenue')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
                
        } elseif ($reportType === 'month') {
            // Báo cáo theo tháng trong năm
            $revenueData = BookingPayment::whereYear('created_at', $year)
                ->where('status', 'success')
                ->selectRaw('MONTH(created_at) as month, COUNT(DISTINCT booking_id) as total_bookings, SUM(amount) as revenue')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
                
        } elseif ($reportType === 'year') {
            // Báo cáo theo năm
            $revenueData = BookingPayment::where('status', 'success')
                ->selectRaw('YEAR(created_at) as year, COUNT(DISTINCT booking_id) as total_bookings, SUM(amount) as revenue')
                ->groupBy('year')
                ->orderBy('year', 'asc')
                ->get();
                
        } elseif ($reportType === 'custom' && $fromDate && $toDate) {
            // Báo cáo tùy chỉnh theo khoảng thời gian
            $revenueData = BookingPayment::whereBetween('created_at', [$fromDate, $toDate])
                ->where('status', 'success')
                ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT booking_id) as total_bookings, SUM(amount) as revenue')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        }

        // Báo cáo theo tour
        $tourRevenue = BookingPayment::join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('tours', 'schedules.tour_id', '=', 'tours.id')
            ->where('booking_payments.status', 'success')
            ->selectRaw('tours.name, tours.id, COUNT(DISTINCT bookings.id) as total_bookings, SUM(booking_payments.amount) as revenue')
            ->groupBy('tours.id', 'tours.name')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();

        return view('admin.revenue.index', compact(
            'totalStats',
            'revenueData',
            'tourRevenue',
            'reportType',
            'year',
            'month',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Export báo cáo (CSV)
     */
    public function export(Request $request)
    {
        // Implement export logic here
        // For now, just redirect back
        return back()->with('info', 'Chức năng export đang được phát triển.');
    }
}
