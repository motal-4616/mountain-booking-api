<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Dashboard Admin - Route theo role
 */
class DashboardController extends Controller
{
    /**
     * Hiển thị trang dashboard admin - redirect theo role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Route dashboard theo role
        if ($user->isSuperAdmin()) {
            return app(SuperAdminDashboardController::class)->index();
        } elseif ($user->isBookingManager()) {
            return app(BookingManagerDashboardController::class)->index();
        } elseif ($user->isContentManager()) {
            return app(ContentManagerDashboardController::class)->index();
        }
        
        // Fallback nếu role không xác định
        abort(403, 'Bạn không có quyền truy cập vào dashboard admin.');
    }
}
