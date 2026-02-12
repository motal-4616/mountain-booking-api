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
        // Bảng Wishlists - Lưu tour yêu thích của user
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Mỗi user chỉ có thể thêm 1 tour vào wishlist 1 lần
            $table->unique(['user_id', 'tour_id']);
            
            // Indexes
            $table->index('user_id');
            $table->index('tour_id');
        });

        // Bảng Notifications - Thông báo cho user
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Loại thông báo (booking_confirmed, tour_reminder, etc.)
            $table->morphs('notifiable'); // user_id và user_type
            $table->text('data'); // JSON data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['notifiable_id', 'notifiable_type']);
            $table->index('read_at');
        });

        // Bảng Contact Messages - Lưu tin nhắn liên hệ
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'read', 'replied'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
            
            // Index
            $table->index('status');
            $table->index('created_at');
        });

        // Thêm cột avatar và bio cho users
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('address')->nullable()->after('bio');
            $table->date('date_of_birth')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('contact_messages');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'bio', 'address', 'date_of_birth']);
        });
    }
};
