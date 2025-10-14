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
        Schema::create('video_posted_values', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('assembly_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('three_d_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('video_360s', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('benefit_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('diy_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('shoppable_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_posted_values');
        Schema::dropIfExists('shoppable_videos');
        Schema::dropIfExists('diy_videos');
        Schema::dropIfExists('benefit_videos');
        Schema::dropIfExists('video_360s');
        Schema::dropIfExists('three_d_videos');
        Schema::dropIfExists('assembly_videos');
    }
};
