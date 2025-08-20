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
        Schema::create('campaign_tag', function (Blueprint $table) {
            $table->id();
            $table->integer('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_tag');
    }
};
