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
        Schema::create('neweegg_sheet_datas', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('pft', 5, 2)->nullable();
            $table->decimal('roi', 5, 2)->nullable();
            $table->integer('l30')->nullable();
            $table->decimal('dil', 5, 2)->nullable();
            $table->string('buy_link')->nullable();
            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('neweegg_sheet_datas');
    }
};
