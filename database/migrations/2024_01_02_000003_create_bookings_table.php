<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng bookings - Lưu đơn đặt vé của người dùng
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');     // Người đặt
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade'); // Lịch trình
            $table->integer('quantity');               // Số lượng người
            $table->string('contact_name');            // Tên liên hệ
            $table->string('contact_phone');           // SĐT liên hệ
            $table->string('contact_email');           // Email liên hệ
            $table->text('note')->nullable();          // Ghi chú
            $table->decimal('total_price', 12, 0);     // Tổng tiền
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending'); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
