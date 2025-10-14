<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopifyb2c_data_view', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->json('value')->nullable(); // Store NR and other flags as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopifyb2c_data_view');
    }
};
