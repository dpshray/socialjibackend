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
        Schema::create('helpfuls', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('review_id');
            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('vote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpfuls');
    }
};
