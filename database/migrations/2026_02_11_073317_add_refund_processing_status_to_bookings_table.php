<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thay đổi enum status để thêm 'refund_processing' (đang xử lý hoàn tiền)
        // MySQL không hỗ trợ ALTER ENUM dễ dàng, nên dùng raw SQL
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'refund_processing', 'refunded') DEFAULT 'pending'");
        
        // Thêm cột lưu thông tin refund từ VNPay
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('refund_status')->nullable()->after('cancelled_at'); // processing, success, failed
            $table->text('refund_message')->nullable()->after('refund_status'); // Message từ VNPay
            $table->string('refund_transaction_ref')->nullable()->after('refund_message'); // Mã giao dịch hoàn tiền
            $table->timestamp('refund_processed_at')->nullable()->after('refund_transaction_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'refunded') DEFAULT 'pending'");
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['refund_status', 'refund_message', 'refund_transaction_ref', 'refund_processed_at']);
        });
    }
};
