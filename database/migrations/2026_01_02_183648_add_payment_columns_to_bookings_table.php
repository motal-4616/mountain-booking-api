<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Đổi tên cột total_price thành total_amount (nếu chưa có)
            if (Schema::hasColumn('bookings', 'total_price')) {
                $table->renameColumn('total_price', 'total_amount');
            }
            
            // Thông tin đặt cọc
            $table->integer('deposit_percent')->nullable()->after('total_amount')->comment('% đặt cọc: 30 hoặc 50');
            $table->decimal('deposit_amount', 12, 0)->nullable()->after('deposit_percent')->comment('Số tiền đặt cọc');
            $table->decimal('paid_amount', 12, 0)->default(0)->after('deposit_amount')->comment('Số tiền đã thanh toán');
            
            // Trạng thái thanh toán
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])
                  ->default('unpaid')
                  ->after('paid_amount')
                  ->comment('Trạng thái thanh toán');
            
            // Trạng thái đơn hàng (tách riêng khỏi status cũ)
            $table->enum('booking_status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->after('payment_status')
                  ->comment('Trạng thái đơn đặt vé');
            
            // Thông tin xác nhận thanh toán tại điểm tập kết
            $table->timestamp('confirmed_paid_at')->nullable()->after('booking_status')->comment('Thời gian xác nhận thanh toán đủ');
            $table->unsignedBigInteger('confirmed_paid_by')->nullable()->after('confirmed_paid_at')->comment('Admin xác nhận');
            
            // Foreign key
            $table->foreign('confirmed_paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['confirmed_paid_by']);
            
            $table->dropColumn([
                'deposit_percent',
                'deposit_amount',
                'paid_amount',
                'payment_status',
                'booking_status',
                'confirmed_paid_at',
                'confirmed_paid_by'
            ]);
            
            // Đổi lại tên cột
            if (Schema::hasColumn('bookings', 'total_amount')) {
                $table->renameColumn('total_amount', 'total_price');
            }
        });
    }
};
