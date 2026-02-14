<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'video', 'voice', 'location', 'system') DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'voice', 'location', 'system') DEFAULT 'text'");
    }
};
