<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Controller quản lý mã giảm giá (chỉ super_admin)
 */
class CouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Danh sách mã giảm giá
     */
    public function index(Request $request)
    {
        $query = Coupon::query()->with('creator');

        // Tìm kiếm theo mã hoặc tên
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            switch ($status) {
                case 'active':
                    $query->active()->valid()->available();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'expired':
                    $query->where('end_date', '<', now()->toDateString());
                    break;
                case 'upcoming':
                    $query->where('start_date', '>', now()->toDateString());
                    break;
            }
        }

        // Lọc theo loại
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Form tạo mã giảm giá mới
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Lưu mã giảm giá mới
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->boolean('is_active', true);

            $coupon = $this->couponService->createCoupon($data);

            // Gửi thông báo coupon mới cho users
            if ($coupon->is_active) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyCouponCreated($coupon);
            }

            return redirect()->route('admin.coupons.index')
                ->with('success', "Đã tạo mã giảm giá {$coupon->code} thành công!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
    }

    /**
     * Xem chi tiết mã giảm giá
     */
    public function show(Coupon $coupon)
    {
        $coupon->load(['creator', 'bookings' => function ($query) {
            $query->with(['user', 'schedule.tour'])->latest()->take(10);
        }]);

        // Thống kê sử dụng
        $stats = [
            'total_bookings' => $coupon->bookings()->count(),
            'total_discount' => $coupon->bookings()->sum('discount_amount'),
            'total_revenue' => $coupon->bookings()->sum('final_price'),
        ];

        return view('admin.coupons.show', compact('coupon', 'stats'));
    }

    /**
     * Form chỉnh sửa mã giảm giá
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Cập nhật mã giảm giá
     */
    public function update(Request $request, Coupon $coupon)
    {
        try {
            $data = $request->all();
            $data['is_active'] = $request->boolean('is_active', true);

            $this->couponService->updateCoupon($coupon, $data);

            return redirect()->route('admin.coupons.index')
                ->with('success', "Đã cập nhật mã giảm giá {$coupon->code} thành công!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
    }

    /**
     * Xóa mã giảm giá
     */
    public function destroy(Coupon $coupon)
    {
        // Kiểm tra đã có booking sử dụng chưa
        if ($coupon->bookings()->exists()) {
            return back()->with('error', 'Không thể xóa mã giảm giá đã được sử dụng. Hãy tắt trạng thái thay vì xóa.');
        }

        $code = $coupon->code;
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', "Đã xóa mã giảm giá {$code}.");
    }

    /**
     * Bật/tắt trạng thái mã giảm giá
     */
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        
        $status = $coupon->is_active ? 'bật' : 'tắt';
        return back()->with('success', "Đã {$status} mã giảm giá {$coupon->code}.");
    }

    /**
     * Tạo mã ngẫu nhiên (AJAX)
     */
    public function generateCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (Coupon::where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }
}
