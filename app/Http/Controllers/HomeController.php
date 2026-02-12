<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;

/**
 * Controller xử lý trang chủ
 */
class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ với danh sách tour
     */
    public function index()
    {
        // Lấy các tour đang hoạt động
        $tours = Tour::where('is_active', true)
            ->with(['schedules' => function($query) {
                $query->where('is_active', true)
                    ->where('departure_date', '>=', now())
                    ->where('available_slots', '>', 0);
            }])
            ->get();

        return view('home', compact('tours'));
    }
}
