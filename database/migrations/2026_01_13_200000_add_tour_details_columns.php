<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm các cột chi tiết cho tour
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->text('overview')->nullable()->after('description');           // Tổng quan chi tiết
            $table->json('itinerary')->nullable()->after('overview');             // Lịch trình chi tiết
            $table->text('includes')->nullable()->after('itinerary');             // Dịch vụ bao gồm
            $table->text('excludes')->nullable()->after('includes');              // Dịch vụ không bao gồm
            $table->text('highlights')->nullable()->after('excludes');            // Điểm nổi bật
            $table->integer('altitude')->nullable()->after('location');           // Độ cao (m)
            $table->string('best_time')->nullable()->after('altitude');           // Thời điểm đẹp nhất
            $table->json('gallery')->nullable()->after('image');                  // Bộ sưu tập ảnh
            $table->decimal('map_lat', 10, 8)->nullable()->after('best_time');    // Vĩ độ
            $table->decimal('map_lng', 11, 8)->nullable()->after('map_lat');      // Kinh độ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'overview', 'itinerary', 'includes', 'excludes', 
                'highlights', 'altitude', 'best_time', 'gallery',
                'map_lat', 'map_lng'
            ]);
        });
    }
};
