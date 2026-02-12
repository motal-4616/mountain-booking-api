<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('mood', ['happy', 'excited', 'peaceful', 'tired', 'sad', 'challenged'])->nullable();
            $table->string('weather')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('altitude')->nullable();
            $table->json('images')->nullable();
            $table->enum('privacy', ['private', 'friends', 'public'])->default('private');
            $table->foreignId('tour_id')->nullable()->constrained('tours')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('privacy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
