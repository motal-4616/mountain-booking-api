<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động cập nhật booking đã hoàn thành mỗi ngày lúc 00:00
Schedule::command('bookings:update-completed')->daily();

// Kiểm tra schedules đã đến hạn đăng ký - chạy mỗi giờ để real-time hơn
Schedule::command('schedule:check-minimum')->hourly();

