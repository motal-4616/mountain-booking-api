<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm unique constraint cho tours (tránh trùng tên + địa điểm)
        Schema::table('tours', function (Blueprint $table) {
            // Xóa các tour trùng lặp trước - compatible với MySQL và SQLite
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement('
                    DELETE t1 FROM tours t1
                    INNER JOIN tours t2 
                    WHERE t1.id > t2.id 
                    AND t1.name = t2.name 
                    AND t1.location = t2.location
                ');
            } else {
                $duplicates = DB::select('
                    SELECT t1.id
                    FROM tours t1
                    INNER JOIN tours t2 ON t1.name = t2.name AND t1.location = t2.location
                    WHERE t1.id > t2.id
                ');
                foreach ($duplicates as $dup) {
                    DB::table('tours')->where('id', $dup->id)->delete();
                }
            }
            
            // Thêm unique constraint
            $table->unique(['name', 'location'], 'tours_name_location_unique');
        });

        // 2. Thêm unique constraint cho schedules (tránh trùng tour + ngày khởi hành)
        Schema::table('schedules', function (Blueprint $table) {
            // Xóa các schedule trùng lặp trước - compatible với MySQL và SQLite
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement('
                    DELETE s1 FROM schedules s1
                    INNER JOIN schedules s2 
                    WHERE s1.id > s2.id 
                    AND s1.tour_id = s2.tour_id 
                    AND s1.departure_date = s2.departure_date
                ');
            } else {
                $duplicates = DB::select('
                    SELECT s1.id
                    FROM schedules s1
                    INNER JOIN schedules s2 ON s1.tour_id = s2.tour_id AND s1.departure_date = s2.departure_date
                    WHERE s1.id > s2.id
                ');
                foreach ($duplicates as $dup) {
                    DB::table('schedules')->where('id', $dup->id)->delete();
                }
            }
            
            // Thêm unique constraint
            $table->unique(['tour_id', 'departure_date'], 'schedules_tour_date_unique');
        });

        // 3. Thêm indexes cho performance
        Schema::table('tours', function (Blueprint $table) {
            $table->index('is_active', 'tours_is_active_index');
            $table->index('created_at', 'tours_created_at_index');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index('tour_id', 'schedules_tour_id_index');
            $table->index('departure_date', 'schedules_departure_date_index');
            $table->index('is_active', 'schedules_is_active_index');
            $table->index(['is_active', 'departure_date'], 'schedules_active_date_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('user_id', 'bookings_user_id_index');
            $table->index('schedule_id', 'bookings_schedule_id_index');
            $table->index('booking_status', 'bookings_booking_status_index');
            $table->index('payment_status', 'bookings_payment_status_index');
            $table->index('created_at', 'bookings_created_at_index');
            $table->index(['user_id', 'created_at'], 'bookings_user_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('created_at', 'users_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique constraints
        Schema::table('tours', function (Blueprint $table) {
            $table->dropUnique('tours_name_location_unique');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique('schedules_tour_date_unique');
        });

        // Drop indexes
        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex('tours_is_active_index');
            $table->dropIndex('tours_created_at_index');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_tour_id_index');
            $table->dropIndex('schedules_departure_date_index');
            $table->dropIndex('schedules_is_active_index');
            $table->dropIndex('schedules_active_date_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_user_id_index');
            $table->dropIndex('bookings_schedule_id_index');
            $table->dropIndex('bookings_booking_status_index');
            $table->dropIndex('bookings_payment_status_index');
            $table->dropIndex('bookings_created_at_index');
            $table->dropIndex('bookings_user_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_created_at_index');
        });
    }
};
