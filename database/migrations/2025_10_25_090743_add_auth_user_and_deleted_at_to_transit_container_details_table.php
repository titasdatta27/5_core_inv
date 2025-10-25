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
        Schema::table('transit_container_details', function (Blueprint $table) {
            $table->string('auth_user')->nullable()->after('comparison_link');
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transit_container_details', function (Blueprint $table) {
            $table->dropColumn('auth_user');
            $table->dropSoftDeletes();
        });
    }
};
