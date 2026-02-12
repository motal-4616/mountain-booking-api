<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng tours - Lưu thông tin các tour leo núi
     */
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Tên tour
            $table->string('image')->nullable();       // Ảnh tour
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium'); // Độ khó
            $table->string('duration');                // Thời gian (VD: "2 ngày 1 đêm")
            $table->decimal('price', 12, 0);           // Giá vé (VNĐ)
            $table->text('description')->nullable();   // Mô tả tour
            $table->string('location');                // Địa điểm
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
