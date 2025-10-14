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
        Schema::create('bestbuy_usa_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->nullable();
            $table->integer('m_l30')->nullable();
            $table->integer('m_l60')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bestbuy_usa_products');
    }
};
