<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Tour;
use Illuminate\Http\Request;

/**
 * Controller quản lý Lịch trình (Admin)
 */
class ScheduleController extends Controller
{
    /**
     * Danh sách lịch trình với bộ lọc
     */
    public function index(Request $request)
    {
        // Query với bộ lọc
        $query = Schedule::select(['id', 'tour_id', 'departure_date', 'end_date', 'max_people', 'available_slots', 'price', 'is_active', 'created_at'])
            ->with(['tour:id,name,location'])
            ->withCount('bookings');

        // Lọc theo tour
        if ($request->filled('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        // Lọc theo ngày bắt đầu
        if ($request->filled('from_date')) {
            $query->whereDate('departure_date', '>=', $request->from_date);
        }

        // Lọc theo ngày kết thúc
        if ($request->filled('to_date')) {
            $query->whereDate('departure_date', '<=', $request->to_date);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->whereDate('departure_date', '>=', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'past') {
                $query->whereDate('departure_date', '<', now());
            }
        }

        $schedules = $query->orderBy('departure_date', 'desc')->paginate(10);
        
        // Lấy danh sách tour cho dropdown
        $tours = Tour::where('is_active', true)->select('id', 'name')->get();

        return view('admin.schedules.index', compact('schedules', 'tours'));
    }

    /**
     * Form thêm lịch trình mới
     */
    public function create()
    {
        $tours = Tour::where('is_active', true)->get();
        return view('admin.schedules.create', compact('tours'));
    }

    /**
     * Lưu lịch trình mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'departure_date' => 'required|date|after:today',
            'end_date' => 'nullable|date|after_or_equal:departure_date',
            'max_people' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ], [
            'tour_id.required' => 'Vui lòng chọn tour.',
            'tour_id.exists' => 'Tour không tồn tại.',
            'departure_date.required' => 'Vui lòng chọn ngày khởi hành.',
            'departure_date.after' => 'Ngày khởi hành phải sau hôm nay.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi hành.',
            'max_people.required' => 'Vui lòng nhập số người tối đa.',
            'max_people.min' => 'Số người tối đa phải ít nhất là 1.',
            'price.required' => 'Vui lòng nhập giá tour.',
            'price.min' => 'Giá tour không được âm.',
        ]);

        // Kiểm tra duration có khớp với tour không
        $tour = Tour::findOrFail($request->tour_id);
        if ($request->end_date) {
            $days = \Carbon\Carbon::parse($request->departure_date)
                ->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;
            
            // Nếu duration khác và chưa confirm, yêu cầu confirm
            if ($days != $tour->duration_days && $request->input('duration_confirmed') != '1') {
                $badgeType = $days > $tour->duration_days ? 'Mở rộng' : 'Rút gọn';
                return back()->withInput()
                    ->with('duration_warning', [
                        'standard' => $tour->duration_days,
                        'current' => $days,
                        'badge' => $badgeType
                    ]);
            }
        }

        // Kiểm tra trùng lặp tour + ngày khởi hành
        $exists = Schedule::where('tour_id', $request->tour_id)
            ->whereDate('departure_date', $request->departure_date)
            ->exists();
        
        if ($exists) {
            return back()->withInput()
                ->with('error', 'Lịch trình cho tour này vào ngày đã chọn đã tồn tại.');
        }

        Schedule::create([
            'tour_id' => $request->tour_id,
            'departure_date' => $request->departure_date,
            'end_date' => $request->end_date,
            'max_people' => $request->max_people,
            'available_slots' => $request->max_people, // Ban đầu = max
            'price' => $request->price,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Thêm lịch trình thành công!');
    }

    /**
     * Form sửa lịch trình
     */
    public function edit(Schedule $schedule)
    {
        $tours = Tour::where('is_active', true)->get();
        return view('admin.schedules.edit', compact('schedule', 'tours'));
    }

    /**
     * Chi tiết lịch trình
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['tour', 'bookings.user']);
        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Cập nhật lịch trình
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'departure_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:departure_date',
            'max_people' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ], [
            'price.required' => 'Vui lòng nhập giá tour.',
            'price.min' => 'Giá tour không được âm.',
        ]);

        // Kiểm tra trùng lặp tour + ngày khởi hành (trừ schedule hiện tại)
        $exists = Schedule::where('tour_id', $request->tour_id)
            ->whereDate('departure_date', $request->departure_date)
            ->where('id', '!=', $schedule->id)
            ->exists();
        
        if ($exists) {
            return back()->withInput()
                ->with('error', 'Lịch trình cho tour này vào ngày đã chọn đã tồn tại.');
        }

        // Tính lại số chỗ trống nếu thay đổi max_people
        $bookedSlots = $schedule->max_people - $schedule->available_slots;
        $newAvailableSlots = $request->max_people - $bookedSlots;

        if ($newAvailableSlots < 0) {
            return back()->withInput()
                ->with('error', 'Số người tối đa không thể nhỏ hơn số đã đặt (' . $bookedSlots . ').');
        }

        $schedule->update([
            'tour_id' => $request->tour_id,
            'departure_date' => $request->departure_date,
            'end_date' => $request->end_date,
            'max_people' => $request->max_people,
            'available_slots' => $newAvailableSlots,
            'price' => $request->price,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Cập nhật lịch trình thành công!');
    }

    /**
     * Xóa lịch trình
     */
    public function destroy(Schedule $schedule)
    {
        // Kiểm tra có booking chưa hủy không
        $hasActiveBookings = $schedule->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Không thể xóa lịch trình đã có đơn đặt vé.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Xóa lịch trình thành công!');
    }

    /**
     * Xử lý hành động hàng loạt
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        $ids = explode(',', $request->ids);
        $count = 0;

        switch ($request->action) {
            case 'activate':
                $count = Schedule::whereIn('id', $ids)->update(['is_active' => true]);
                return redirect()->route('admin.schedules.index')
                    ->with('success', "Kích hoạt thành công {$count} lịch trình!");

            case 'deactivate':
                $count = Schedule::whereIn('id', $ids)->update(['is_active' => false]);
                return redirect()->route('admin.schedules.index')
                    ->with('success', "Tạm dừng thành công {$count} lịch trình!");

            case 'delete':
                // Kiểm tra có booking active không
                $schedulesWithBookings = Schedule::whereIn('id', $ids)
                    ->whereHas('bookings', function($query) {
                        $query->whereIn('status', ['pending', 'confirmed']);
                    })
                    ->count();

                if ($schedulesWithBookings > 0) {
                    return back()->with('error', 
                        "Có {$schedulesWithBookings} lịch trình đang có đơn đặt vé, không thể xóa!");
                }

                $count = Schedule::whereIn('id', $ids)->delete();
                return redirect()->route('admin.schedules.index')
                    ->with('success', "Xóa thành công {$count} lịch trình!");

            default:
                return back()->with('error', 'Hành động không hợp lệ!');
        }
    }
}
