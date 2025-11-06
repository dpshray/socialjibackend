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
        Schema::create('campaign_entity_trustap_transactions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('bidder_id');
            $table->foreign('bidder_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('bid_id');
            $table->foreign('bid_id')->references('id')->on('bids')->cascadeOnDelete()->cascadeOnUpdate();
            // $table->foreignId('gig_id')->constrained('gigs');
            // $table->integer('gig_pricing_id');
            // $table->foreign('gig_pricing_id')->references('id')->on('gig_pricing')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('gig_pricing_id')->constrained('gig_pricing');
            $table->string('campaign_title');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('transactionId')->unique();
            $table->string('transactionType');

            $table->string('sellerId');
            $table->string('buyerId');

            $table->string('status');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('charge');
            $table->unsignedBigInteger('chargeSeller');
            
            $table->string('currency');
            
            $table->boolean('claimedBySeller')->default(false);
            $table->boolean('claimedByBuyer')->default(false);
            $table->dateTime('delivered_at')->nullable();

            $table->timestamp('complaintPeriodDeadline')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_entity_trustap_transactions');
    }
};
