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
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->json('requirements')->nullable();
            $table->json('features')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);   // archived, active
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gig_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs');
            $table->foreignId('pricing_tier_id')->constrained('pricing_tiers');
            $table->string('price')->nullable();
            $table->string('delivery_time')->nullable();
            $table->text('description')->nullable();
            $table->text('requirement')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gig_pricing');
        Schema::dropIfExists('gigs');
        Schema::dropIfExists('pricing_tiers');
    }
};
