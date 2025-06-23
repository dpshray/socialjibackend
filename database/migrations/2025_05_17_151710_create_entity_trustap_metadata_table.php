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
        Schema::create('entity_trustap_metadata', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('gig_id');
            $table->foreign('gig_id')->references('id')->on('gigs')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreignId('gig_id')->constrained('gigs');
            $table->boolean('trustapEnabled')->default(false);
            $table->string('transactionType');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_trustap_metadata');
    }
};
