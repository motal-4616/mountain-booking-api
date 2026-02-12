<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Thêm trường lý do hủy đơn
            $table->text('cancellation_reason')->nullable()->after('status');
            // Người hủy đơn (user_id hoặc admin_id)
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancellation_reason');
            // Thời gian hủy
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
        
        // Cập nhật enum status để thêm trạng thái refunded
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
        
        // Khôi phục lại enum status ban đầu
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
