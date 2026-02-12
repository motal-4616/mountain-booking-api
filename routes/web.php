<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\RevenueController;

/*
|--------------------------------------------------------------------------
| Web Routes - Định nghĩa các đường dẫn của website
|--------------------------------------------------------------------------
*/

// ===== TRANG CÔNG KHAI =====

// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Danh sách tour & Chi tiết tour
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{tour}', [TourController::class, 'show'])->name('tours.show');

// Liên hệ (công khai)
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// VNPay Callback (không cần auth vì VNPay redirect)
Route::get('/payment/vnpay/callback', [BookingController::class, 'vnpayCallback'])
    ->name('payment.vnpay.callback');

// VNPay App Return (cho mobile app - không cần auth)
Route::get('/payment/vnpay/app-return', [\App\Http\Controllers\Api\ApiPaymentController::class, 'vnpayAppReturn'])
    ->name('payment.vnpay.app-return');


// ===== XÁC THỰC (ĐĂNG KÝ / ĐĂNG NHẬP) =====

// Chỉ cho phép truy cập khi CHƯA đăng nhập
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Đăng xuất - yêu cầu đã đăng nhập
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


// ===== KHU VỰC NGƯỜI DÙNG (YÊU CẦU ĐĂNG NHẬP) =====

Route::middleware('auth')->group(function () {
    // Đặt vé
    Route::get('/booking/create/{schedule}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
    
    // Thanh toán
    Route::get('/booking/{booking}/payment', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::post('/booking/{booking}/process-payment', [BookingController::class, 'processPayment'])->name('bookings.processPayment');
    
    // VNPay Payment
    Route::post('/booking/{booking}/payment/vnpay', [BookingController::class, 'processPaymentVNPay'])
        ->name('bookings.payment.vnpay');

    // Quản lý vé đã đặt
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/my-bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/booking/{booking}/success', [BookingController::class, 'success'])->name('bookings.success');
    Route::patch('/my-bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    
    // ===== TRANG CÁ NHÂN =====
    Route::get('/profile', [UserProfileController::class, 'index'])->name('user.profile');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password');
    Route::post('/profile/avatar', [UserProfileController::class, 'updateAvatar'])->name('user.profile.avatar');
    
    // ===== WISHLIST =====
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('user.wishlist');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('user.wishlist.toggle');
    Route::get('/wishlist/sync', [WishlistController::class, 'sync'])->name('user.wishlist.sync');
    
    // ===== REVIEWS / ĐÁNH GIÁ =====
    Route::get('/reviews/my-reviews', [ReviewController::class, 'myReviews'])->name('reviews.my-reviews');
    Route::get('/tours/{tour}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::get('/booking/{booking}/review', [ReviewController::class, 'createFromBooking'])->name('reviews.create-from-booking');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // ===== COUPON / MÃ GIẢM GIÁ (AJAX) =====
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');
    
    // ===== NOTIFICATIONS / THÔNG BÁO =====
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [NotificationController::class, 'getLatest'])->name('notifications.latest');
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/read/all', [NotificationController::class, 'destroyRead'])->name('notifications.destroy-read');
});


// ===== KHU VỰC ADMIN (YÊU CẦU QUYỀN ADMIN) =====

Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {

    // Dashboard - Route theo role
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard riêng cho từng role (optional - nếu muốn access trực tiếp)
    Route::get('/dashboard/superadmin', [\App\Http\Controllers\Admin\SuperAdminDashboardController::class, 'index'])
        ->middleware('super_admin')
        ->name('dashboard.superadmin');
    
    Route::get('/dashboard/booking-manager', [\App\Http\Controllers\Admin\BookingManagerDashboardController::class, 'index'])
        ->middleware('booking_manager')
        ->name('dashboard.booking-manager');
    
    Route::get('/dashboard/content-manager', [\App\Http\Controllers\Admin\ContentManagerDashboardController::class, 'index'])
        ->middleware('content_manager')
        ->name('dashboard.content-manager');

    // Quản lý Tour
    Route::post('/tours/bulk-action', [AdminTourController::class, 'bulkAction'])->name('tours.bulk-action');
    Route::resource('tours', AdminTourController::class);

    // Quản lý Lịch trình
    Route::post('/schedules/bulk-action', [AdminScheduleController::class, 'bulkAction'])->name('schedules.bulk-action');
    Route::resource('schedules', AdminScheduleController::class);

    // Quản lý Đơn đặt vé
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{booking}/confirm-payment', [AdminBookingController::class, 'confirmPayment'])->name('bookings.confirmPayment');
    Route::patch('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/retry-refund', [AdminBookingController::class, 'retryRefund'])->name('bookings.retryRefund');
    Route::post('/bookings/{booking}/manual-refund', [AdminBookingController::class, 'manualRefund'])->name('bookings.manualRefund');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
    
    // Quản lý User
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::patch('/users/{user}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('users.toggleBlock');
    
    // Quản lý Đánh giá
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::patch('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/bulk-action', [AdminReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
    
    // Quản lý Liên hệ
    Route::get('/contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contacts.index');
    Route::patch('/contacts/{contact}/mark-read', [\App\Http\Controllers\Admin\ContactController::class, 'markRead'])->name('contacts.markRead');
    Route::delete('/contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('contacts.destroy');
    
    // Báo cáo Doanh thu
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
    Route::get('/revenue/export', [RevenueController::class, 'export'])->name('revenue.export');
    
    // Quản lý Thông báo
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [\App\Http\Controllers\Admin\NotificationController::class, 'getLatest'])->name('notifications.latest');
    Route::get('/notifications/count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/delete-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'deleteAllRead'])->name('notifications.deleteAllRead');
    
    // ===== QUẢN LÝ MÃ GIẢM GIÁ (CHỈ SUPER_ADMIN) =====
    Route::middleware('super_admin')->group(function () {
        Route::resource('coupons', AdminCouponController::class);
        Route::patch('/coupons/{coupon}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggleStatus');
        Route::get('/coupons-generate-code', [AdminCouponController::class, 'generateCode'])->name('coupons.generateCode');
    });
});