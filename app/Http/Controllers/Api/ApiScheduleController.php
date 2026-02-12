<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\Request;

class ApiScheduleController extends ApiController
{
    /**
     * Get list of schedules with filters
     */
    public function index(Request $request)
    {
        $query = Schedule::where('is_active', true)
            ->where('departure_date', '>=', now())
            ->with('tour');

        // Filter by tour
        if ($request->has('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('departure_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('departure_date', '<=', $request->to_date);
        }

        // Filter by month
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('departure_date', $request->month)
                  ->whereYear('departure_date', $request->year);
        }

        // Filter available only
        if ($request->get('available_only', false)) {
            $query->where('available_slots', '>', 0);
        }

        // Sort
        $query->orderBy('departure_date', 'asc');

        $perPage = $request->get('per_page', 20);
        $schedules = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            ScheduleResource::collection($schedules),
            $this->getPaginationMeta($schedules),
            'Lấy danh sách lịch khởi hành thành công'
        );
    }

    /**
     * Get available schedules (còn chỗ)
     */
    public function available(Request $request)
    {
        $query = Schedule::where('is_active', true)
            ->where('departure_date', '>=', now())
            ->where('available_slots', '>', 0)
            ->with('tour')
            ->orderBy('departure_date', 'asc');

        // Filter by tour
        if ($request->has('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        $limit = $request->get('limit', 20);
        $schedules = $query->limit($limit)->get();

        return $this->successResponse(
            ScheduleResource::collection($schedules),
            'Lấy lịch khởi hành còn chỗ thành công'
        );
    }

    /**
     * Get schedule details
     */
    public function show(Schedule $schedule)
    {
        if (!$schedule->is_active) {
            return $this->notFoundResponse('Lịch khởi hành không tồn tại hoặc đã bị ẩn');
        }

        $schedule->load('tour');

        return $this->successResponse(
            new ScheduleResource($schedule),
            'Lấy chi tiết lịch khởi hành thành công'
        );
    }
}
