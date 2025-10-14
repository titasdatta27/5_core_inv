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
        Schema::create('instagram_shop_sheet_data', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->integer('i_l30')->nullable();     // 30-day orders
            $table->integer('i_l60')->nullable();     // 60-day orders
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('views')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_shop_sheet_data');
    }
};
