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
        Schema::create('ratings', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('influencer_id');
            $table->foreign('influencer_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('brand_id');
            $table->foreign('brand_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->tinyInteger('rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
