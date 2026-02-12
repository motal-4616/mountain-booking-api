<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Schedule;
use Illuminate\Http\Request;

/**
 * Controller xử lý hiển thị tour cho người dùng
 */
class TourController extends Controller
{
    /**
     * Hiển thị danh sách tour
     */
    public function index(Request $request)
    {
        $query = Tour::where('is_active', true);

        // Tìm kiếm theo tên hoặc địa điểm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Lọc theo độ khó (hỗ trợ multiple checkboxes)
        if ($request->filled('difficulty')) {
            $difficulties = is_array($request->difficulty) ? $request->difficulty : [$request->difficulty];
            $query->whereIn('difficulty', $difficulties);
        }

        // Lọc theo khoảng giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'difficulty':
                $query->orderByRaw("FIELD(difficulty, 'easy', 'medium', 'hard')");
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $tours = $query->withCount(['schedules' => function($q) {
                $q->where('is_active', true)
                    ->where('departure_date', '>=', now())
                    ->where('available_slots', '>', 0);
            }])
            ->paginate(9)
            ->appends($request->except('page'));

        return view('tours.index', compact('tours'));
    }

    /**
     * Hiển thị chi tiết tour
     */
    public function show(Request $request, Tour $tour)
    {
        // Kiểm tra tour còn hoạt động không
        if (!$tour->is_active) {
            abort(404, 'Tour không tồn tại hoặc đã ngừng hoạt động.');
        }

        // Lấy các lịch trình còn active và chưa khởi hành
        $schedules = $tour->schedules()
            ->where('is_active', true)
            ->where('departure_date', '>=', now())
            ->orderBy('departure_date')
            ->get();

        // Lấy schedule được chọn (nếu có)
        $selectedSchedule = null;
        if ($request->filled('schedule_id')) {
            $selectedSchedule = $schedules->firstWhere('id', $request->schedule_id);
        }

        return view('tours.show', compact('tour', 'schedules', 'selectedSchedule'));
    }
}
