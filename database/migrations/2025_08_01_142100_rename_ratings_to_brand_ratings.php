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
        Schema::table('brand_ratings', function (Blueprint $table) {
            Schema::rename('ratings', 'brand_ratings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_ratings', function (Blueprint $table) {
            Schema::rename('brand_ratings', 'ratings');
        });
    }
};
