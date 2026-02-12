<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các cột coupon vào bảng bookings
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Coupon liên kết
            $table->foreignId('coupon_id')->nullable()->after('schedule_id')
                  ->constrained('coupons')->onDelete('set null');
            
            // Số tiền được giảm
            $table->decimal('discount_amount', 12, 0)->default(0)->after('total_amount');
            
            // Giá cuối cùng sau khi áp dụng giảm giá
            $table->decimal('final_price', 12, 0)->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_amount', 'final_price']);
        });
    }
};
