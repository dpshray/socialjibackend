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
        Schema::create('entity_trustap_transactions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('gig_id');
            $table->foreign('gig_id')->references('id')->on('gigs')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('gig_id')->constrained('gigs');
            $table->integer('gig_pricing_id');
            $table->foreign('gig_pricing_id')->references('id')->on('gig_pricing')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('gig_pricing_id')->constrained('gig_pricing');
            $table->string('gig_title');
            $table->text('descripion')->nullable();

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
        Schema::dropIfExists('entity_trustap_transactions');
    }
};
