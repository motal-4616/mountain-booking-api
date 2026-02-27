<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiTourController;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiBookingController;
use App\Http\Controllers\Api\ApiPaymentController;
use App\Http\Controllers\Api\ApiProfileController;
use App\Http\Controllers\Api\ApiReviewController;
use App\Http\Controllers\Api\ApiWishlistController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\Api\ApiCouponController;
use App\Http\Controllers\Api\ApiContactController;
use App\Http\Controllers\Api\ApiConfigController;
use App\Http\Controllers\Api\ApiBlogController;
use App\Http\Controllers\Api\ApiFriendController;
use App\Http\Controllers\Api\ApiChatController;
use App\Http\Controllers\Api\ApiJournalController;
use App\Http\Controllers\Api\ApiLevelController;

/*
|--------------------------------------------------------------------------
| API Routes - Mobile App & External APIs
|--------------------------------------------------------------------------
*/

// ===== PUBLIC ROUTES (No Authentication Required) =====

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);
});

// Tours - Public browsing
Route::prefix('tours')->group(function () {
    Route::get('/', [ApiTourController::class, 'index']);
    Route::get('/featured', [ApiTourController::class, 'featured']);
    Route::get('/popular', [ApiTourController::class, 'popular']);
    Route::get('/{tour}', [ApiTourController::class, 'show']);
    Route::get('/{tour}/schedules', [ApiTourController::class, 'getSchedules']);
    Route::get('/{tour}/reviews', [ApiTourController::class, 'getReviews']);
});

// Schedules - Public browsing
Route::prefix('schedules')->group(function () {
    Route::get('/', [ApiScheduleController::class, 'index']);
    Route::get('/available', [ApiScheduleController::class, 'available']);
    Route::get('/{schedule}', [ApiScheduleController::class, 'show']);
});

// Contact
Route::post('/contact', [ApiContactController::class, 'store']);

// App Configuration
Route::get('/config', [ApiConfigController::class, 'index']);

// Search
Route::get('/search', [ApiTourController::class, 'search']);

// VNPay Callback (webhook - không cần auth vì từ VNPay)
Route::get('/payments/vnpay/callback', [ApiPaymentController::class, 'vnpayCallback']);


// ===== PROTECTED ROUTES (Require Authentication) =====

Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::post('/refresh', [ApiAuthController::class, 'refresh']);
        Route::get('/me', [ApiAuthController::class, 'me']);
    });

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ApiProfileController::class, 'show']);
        Route::put('/', [ApiProfileController::class, 'update']);
        Route::put('/password', [ApiProfileController::class, 'updatePassword']);
        Route::post('/avatar', [ApiProfileController::class, 'updateAvatar']);
    });

    // User Levels
    Route::prefix('levels')->group(function () {
        Route::get('/', [ApiLevelController::class, 'index']);
        Route::get('/my-level', [ApiLevelController::class, 'myLevel']);
    });

    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::get('/', [ApiBookingController::class, 'index']);
        Route::get('/statistics', [ApiBookingController::class, 'statistics']);
        Route::get('/{booking}', [ApiBookingController::class, 'show']);
        Route::post('/', [ApiBookingController::class, 'store']);
        Route::patch('/{booking}/cancel', [ApiBookingController::class, 'cancel']);
        Route::get('/{booking}/ticket', [ApiBookingController::class, 'downloadTicket']);
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::post('/vnpay', [ApiPaymentController::class, 'createVNPayPayment']);
        Route::post('/cash', [ApiPaymentController::class, 'createCashPayment']);
        Route::get('/{booking}', [ApiPaymentController::class, 'getPaymentHistory']);
    });

    // Coupons
    Route::prefix('coupons')->group(function () {
        Route::get('/available', [ApiCouponController::class, 'available']);
        Route::post('/validate', [ApiCouponController::class, 'validate']);
        Route::post('/apply', [ApiCouponController::class, 'apply']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/my-reviews', [ApiReviewController::class, 'myReviews']);
        Route::post('/', [ApiReviewController::class, 'store']);
        Route::put('/{review}', [ApiReviewController::class, 'update']);
        Route::delete('/{review}', [ApiReviewController::class, 'destroy']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [ApiWishlistController::class, 'index']);
        Route::post('/toggle', [ApiWishlistController::class, 'toggle']);
        Route::get('/check/{tour}', [ApiWishlistController::class, 'check']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [ApiNotificationController::class, 'index']);
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [ApiNotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [ApiNotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [ApiNotificationController::class, 'destroy']);
    });

    // Blog
    Route::prefix('blog')->group(function () {
        Route::get('/categories', [ApiBlogController::class, 'categories']);
        Route::get('/my-posts', [ApiBlogController::class, 'myPosts']);
        Route::get('/user/{userId}', [ApiBlogController::class, 'userPosts']);
        Route::get('/', [ApiBlogController::class, 'index']);
        Route::get('/{slug}', [ApiBlogController::class, 'show']);
        Route::post('/', [ApiBlogController::class, 'store']);
        Route::put('/{id}', [ApiBlogController::class, 'update']);
        Route::post('/{id}/update', [ApiBlogController::class, 'update']);
        Route::delete('/{id}', [ApiBlogController::class, 'destroy']);
        Route::post('/{id}/like', [ApiBlogController::class, 'toggleLike']);
        Route::post('/{id}/comment', [ApiBlogController::class, 'comment']);
        Route::delete('/comment/{commentId}', [ApiBlogController::class, 'deleteComment']);
        Route::post('/comment/{commentId}/like', [ApiBlogController::class, 'toggleCommentLike']);
    });

    // Friends
    Route::prefix('friends')->group(function () {
        Route::get('/', [ApiFriendController::class, 'index']);
        Route::get('/requests', [ApiFriendController::class, 'pendingRequests']);
        Route::get('/sent', [ApiFriendController::class, 'sentRequests']);
        Route::get('/search', [ApiFriendController::class, 'searchUsers']);
        Route::get('/suggestions', [ApiFriendController::class, 'suggestions']);
        Route::get('/profile/{userId}', [ApiFriendController::class, 'profile']);
        Route::post('/request', [ApiFriendController::class, 'sendRequest']);
        Route::post('/{friendshipId}/accept', [ApiFriendController::class, 'acceptRequest']);
        Route::post('/{friendshipId}/reject', [ApiFriendController::class, 'rejectRequest']);
        Route::delete('/{friendshipId}/cancel', [ApiFriendController::class, 'cancelRequest']);
        Route::delete('/{userId}/unfriend', [ApiFriendController::class, 'unfriend']);
        Route::post('/{userId}/block', [ApiFriendController::class, 'block']);
        Route::delete('/{userId}/unblock', [ApiFriendController::class, 'unblock']);
        Route::post('/{userId}/follow', [ApiFriendController::class, 'toggleFollow']);
    });

    // Chat
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ApiChatController::class, 'conversations']);
        Route::post('/conversations/private', [ApiChatController::class, 'getOrCreatePrivateConversation']);
        Route::get('/conversations/{conversationId}/messages', [ApiChatController::class, 'messages']);
        Route::post('/conversations/{conversationId}/messages', [ApiChatController::class, 'sendMessage']);
        Route::post('/conversations/{conversationId}/read', [ApiChatController::class, 'markAsRead']);
        Route::post('/conversations/{conversationId}/mute', [ApiChatController::class, 'toggleMute']);
        Route::delete('/messages/{messageId}', [ApiChatController::class, 'deleteMessage']);
        Route::get('/unread-count', [ApiChatController::class, 'unreadCount']);
    });

    // Journals
    Route::prefix('journals')->group(function () {
        Route::get('/my', [ApiJournalController::class, 'myJournals']);
        Route::get('/stats', [ApiJournalController::class, 'stats']);
        Route::get('/', [ApiJournalController::class, 'index']);
        Route::get('/{id}', [ApiJournalController::class, 'show']);
        Route::post('/', [ApiJournalController::class, 'store']);
        Route::put('/{id}', [ApiJournalController::class, 'update']);
        Route::delete('/{id}', [ApiJournalController::class, 'destroy']);
    });
});
