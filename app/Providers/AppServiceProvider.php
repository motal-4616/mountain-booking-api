<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom commands
        $this->commands([
            \App\Console\Commands\CheckSchedules::class,
            \App\Console\Commands\UpdateCompletedBookings::class,
        ]);
        
        // Force HTTPS in production (Railway) or ngrok
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
        
        // Tự động cập nhật booking completed mỗi 24h (dev environment)
        if (config('app.env') === 'local') {
            $lastRun = Cache::get('bookings_completed_last_run');
            $now = Carbon::now();
            
            // Chạy nếu chưa từng chạy hoặc đã qua 24h
            if (!$lastRun || $now->diffInHours(Carbon::parse($lastRun)) >= 24) {
                try {
                    Artisan::call('bookings:update-completed');
                    Cache::put('bookings_completed_last_run', $now->toDateTimeString(), 86400 * 7); // Lưu 7 ngày
                } catch (\Exception $e) {
                    // Bỏ qua lỗi để không ảnh hưởng app
                }
            }
        }
        
        // Share wishlist count with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $wishlistCount = Auth::user()->wishlists()->count();
                $view->with('wishlistCount', $wishlistCount);
            } else {
                $view->with('wishlistCount', 0);
            }
        });
    }
}
