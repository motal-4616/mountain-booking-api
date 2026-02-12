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
        Schema::table('schedules', function (Blueprint $table) {
            $table->integer('min_people')->default(10)->after('price');
            $table->integer('registration_deadline_days')->default(2)->after('min_people')->comment('Số ngày trước khởi hành để đóng đăng ký');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['min_people', 'registration_deadline_days']);
        });
    }
};
