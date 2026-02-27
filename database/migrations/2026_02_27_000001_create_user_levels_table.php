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
        Schema::create('user_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->string('name');
            $table->string('icon');
            $table->string('frame_color')->default('default'); // default, silver, green, gold, diamond, legendary
            $table->integer('required_tours')->default(0);
            $table->integer('required_reviews')->default(0);
            $table->integer('required_blogs')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->json('benefits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
