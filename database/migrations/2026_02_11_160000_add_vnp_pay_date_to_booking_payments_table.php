<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột vnp_pay_date để lưu ngày thanh toán gốc từ VNPay.
 * Đây là giá trị vnp_PayDate mà VNPay trả về trong callback,
 * cần dùng cho API hoàn tiền (vnp_TransactionDate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('vnp_pay_date', 20)->nullable()->after('vnp_transaction_no')
                ->comment('Ngày thanh toán gốc từ VNPay (vnp_PayDate) - format: YmdHis');
        });
    }

    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn('vnp_pay_date');
        });
    }
};
