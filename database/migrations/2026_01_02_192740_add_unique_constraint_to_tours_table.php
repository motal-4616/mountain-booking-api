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
        Schema::table('tours', function (Blueprint $table) {
            // Xóa các tour trùng lặp trước khi add unique constraint
            // Compatible với cả MySQL và SQLite
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
                // SQLite compatible version
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần rollback vì chỉ xóa dữ liệu trùng
    }
};
