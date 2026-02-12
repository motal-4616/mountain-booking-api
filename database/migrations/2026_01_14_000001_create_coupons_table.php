<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng coupons - Mã giảm giá
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();                    // Mã giảm giá (unique)
            $table->string('name');                                  // Tên mã giảm giá
            $table->text('description')->nullable();                 // Mô tả
            $table->enum('type', ['percent', 'fixed'])->default('percent'); // Loại: phần trăm hoặc cố định
            $table->decimal('value', 12, 0);                         // Giá trị giảm (% hoặc VNĐ)
            $table->decimal('min_order_amount', 12, 0)->default(0);  // Đơn hàng tối thiểu
            $table->decimal('max_discount', 12, 0)->nullable();      // Giảm tối đa (cho type=percent)
            $table->integer('usage_limit')->nullable();              // Giới hạn lượt sử dụng (null = không giới hạn)
            $table->integer('used_count')->default(0);               // Số lần đã sử dụng
            $table->date('start_date');                              // Ngày bắt đầu
            $table->date('end_date');                                // Ngày kết thúc
            $table->boolean('is_active')->default(true);             // Trạng thái bật/tắt
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Admin tạo
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
