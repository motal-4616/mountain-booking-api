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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('rating')->unsigned()->comment('Đánh giá từ 1-5 sao');
            $table->text('comment')->nullable()->comment('Nhận xét chi tiết');
            $table->string('title', 255)->nullable()->comment('Tiêu đề đánh giá');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Trạng thái duyệt');
            $table->text('admin_note')->nullable()->comment('Ghi chú của admin');
            $table->timestamps();
            
            // Index để tối ưu query
            $table->index(['tour_id', 'status']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
