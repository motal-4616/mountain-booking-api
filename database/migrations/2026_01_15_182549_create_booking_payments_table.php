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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            
            // Loại thanh toán: deposit (đặt cọc), full (trả đủ), remaining (trả nốt)
            $table->enum('payment_type', ['deposit', 'full', 'remaining'])->default('full');
            
            // Phương thức: vnpay, cash (tiền mặt), transfer (chuyển khoản)
            $table->enum('payment_method', ['vnpay', 'cash', 'transfer'])->default('vnpay');
            
            // Số tiền thanh toán
            $table->decimal('amount', 12, 0)->default(0);
            
            // Trạng thái: pending (chờ), success (thành công), failed (thất bại), refunded (hoàn tiền)
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            
            // Mã giao dịch VNPay (nếu có)
            $table->string('transaction_ref')->nullable();
            $table->string('vnpay_response_code')->nullable();
            
            // Admin xác nhận (cho cash/transfer)
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            
            // Ghi chú
            $table->text('note')->nullable();
            
            $table->timestamps();
            
            // Index để query nhanh
            $table->index(['booking_id', 'status']);
            $table->index('transaction_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
