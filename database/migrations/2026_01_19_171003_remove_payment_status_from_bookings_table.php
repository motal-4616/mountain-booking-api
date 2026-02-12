<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa các cột payment cũ vì giờ dùng bảng booking_payments
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Xóa foreign key trước
            if (Schema::hasColumn('bookings', 'confirmed_paid_by')) {
                $table->dropForeign(['confirmed_paid_by']);
            }
            
            // Xóa các cột payment cũ
            $columns = ['payment_status', 'deposit_percent', 'deposit_amount', 'paid_amount', 
                       'confirmed_paid_at', 'confirmed_paid_by', 'booking_status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Thêm lại các cột nếu rollback
            $table->integer('deposit_percent')->nullable()->after('total_amount');
            $table->decimal('deposit_amount', 12, 0)->nullable()->after('deposit_percent');
            $table->decimal('paid_amount', 12, 0)->default(0)->after('deposit_amount');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])
                  ->default('unpaid')->after('paid_amount');
            $table->enum('booking_status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')->after('payment_status');
            $table->timestamp('confirmed_paid_at')->nullable()->after('booking_status');
            $table->unsignedBigInteger('confirmed_paid_by')->nullable()->after('confirmed_paid_at');
            $table->foreign('confirmed_paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
