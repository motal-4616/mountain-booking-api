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
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Bước 1 - Mở rộng enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'super_admin', 'booking_manager', 'content_manager') DEFAULT 'user'");
            
            // Bước 2: Update các admin cũ thành content_manager
            DB::table('users')
                ->where('role', 'admin')
                ->update(['role' => 'content_manager']);
            
            // Bước 3: Loại bỏ 'admin' khỏi enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'super_admin', 'booking_manager', 'content_manager') DEFAULT 'user'");
        } else {
            // SQLite: Không cần thay đổi enum, chỉ update dữ liệu
            DB::table('users')
                ->where('role', 'admin')
                ->update(['role' => 'content_manager']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Khôi phục lại role cũ
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'super_admin', 'booking_manager', 'content_manager') DEFAULT 'user'");
            
            // Chuyển content_manager về admin
            DB::table('users')
                ->where('role', 'content_manager')
                ->update(['role' => 'admin']);
        } else {
            // SQLite
            DB::table('users')
                ->where('role', 'content_manager')
                ->update(['role' => 'admin']);
        }
    }
};
