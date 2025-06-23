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
        Schema::create('social_profiles', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('user_id')->constrained('users');
            $table->integer('social_site_id');
            $table->foreign('social_site_id')->references('id')->on('social_sites')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('social_site_id')->constrained('social_sites');
            $table->string('profile_url')->nullable();
            $table->integer('follower_count')->nullable();
            $table->integer('following_count')->nullable();
            $table->integer('post_count')->nullable();
            $table->float('avg_like_per_post_count')->nullable();
            $table->float('avg_comment_per_post_count')->nullable();
            $table->float('follower_growth_rate_per_week')->nullable();
            $table->float('highest_like')->nullable();
            $table->float('lowest_like')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_profiles');
    }
};
