<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Schedule;
use App\Models\Review;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard cho Content Manager - Tập trung vào quản lý nội dung
 */
class ContentManagerDashboardController extends Controller
{
    public function index()
    {
        // Thống kê về nội dung
        $stats = [
            // Tours
            'total_tours' => Tour::count(),
            'active_tours' => Tour::where('is_active', true)->count(),
            'inactive_tours' => Tour::where('is_active', false)->count(),
            'tours_this_month' => Tour::whereMonth('created_at', now()->month)->count(),
            
            // Schedules
            'total_schedules' => Schedule::count(),
            'active_schedules' => Schedule::where('is_active', true)->count(),
            'upcoming_schedules' => Schedule::where('departure_date', '>=', now())
                ->where('is_active', true)
                ->count(),
            'past_schedules' => Schedule::where('departure_date', '<', now())->count(),
            
            // Reviews
            'total_reviews' => Review::count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'approved_reviews' => Review::where('status', 'approved')->count(),
            'rejected_reviews' => Review::where('status', 'rejected')->count(),
            'avg_rating' => round(Review::where('status', 'approved')->avg('rating'), 1),
            
            // Contact Messages
            'total_contacts' => ContactMessage::count(),
            'unread_contacts' => ContactMessage::where('status', 'unread')->count(),
            'contacts_this_week' => ContactMessage::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Tours cần cập nhật (không có lịch trình sắp tới)
        $toursNeedSchedule = Tour::whereDoesntHave('schedules', function($q) {
                $q->where('departure_date', '>=', now())
                  ->where('is_active', true);
            })
            ->where('is_active', true)
            ->limit(5)
            ->get();

        // Reviews cần duyệt
        $pendingReviews = Review::with(['user', 'tour', 'booking'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Tours phổ biến nhất (theo số lượng booking)
        $popularTours = Tour::withCount(['schedules as bookings_count' => function($q) {
                $q->join('bookings', 'schedules.id', '=', 'bookings.schedule_id');
            }])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        // Contact messages chưa đọc
        $unreadContacts = ContactMessage::where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Biểu đồ reviews 6 tháng qua
        $reviewChart = $this->getReviewChart();
        
        // Thống kê rating
        $ratingStats = Review::where('status', 'approved')
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->get()
            ->pluck('total', 'rating');

        // Tours mới nhất
        $recentTours = Tour::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Lịch trình cần cập nhật (sắp đầy)
        $almostFullSchedules = Schedule::with('tour')
            ->where('departure_date', '>=', now())
            ->where('is_active', true)
            ->whereRaw('available_slots < max_people * 0.2') // Còn dưới 20% chỗ
            ->orderBy('departure_date')
            ->limit(5)
            ->get();

        return view('admin.dashboards.content-manager', compact(
            'stats',
            'toursNeedSchedule',
            'pendingReviews',
            'popularTours',
            'unreadContacts',
            'reviewChart',
            'ratingStats',
            'recentTours',
            'almostFullSchedules'
        ));
    }

    /**
     * Biểu đồ reviews 6 tháng gần đây
     */
    private function getReviewChart()
    {
        $months = [];
        $reviews = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $count = Review::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $reviews[] = $count;
        }
        
        return [
            'labels' => $months,
            'data' => $reviews
        ];
    }
}
