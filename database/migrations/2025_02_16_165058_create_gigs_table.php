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
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('features')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);   // archived, active
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gig_pricing', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('gig_id');
            $table->foreign('gig_id')->references('id')->on('gigs')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('gig_id')->constrained('gigs');
            $table->integer('pricing_tier_id');
            $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('pricing_tier_id')->constrained('pricing_tiers');
            $table->decimal('price',10,2);
            $table->integer('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('delivery_time')->nullable();
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
