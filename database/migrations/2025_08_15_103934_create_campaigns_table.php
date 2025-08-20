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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('brand_id');
            $table->foreign('brand_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title');
            $table->text('description');
            $table->string('categories');
            $table->text('eligibility');
            $table->text('requirement');
            $table->decimal('price',10,2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
