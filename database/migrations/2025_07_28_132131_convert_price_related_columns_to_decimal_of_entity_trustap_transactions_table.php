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
        Schema::table('entity_trustap_transactions', function (Blueprint $table) {
            $table->decimal('price',10,2)->change();
            $table->decimal('charge',10,2)->change();
            $table->decimal('chargeSeller',10,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_trustap_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedBigInteger('charge')->change();
            $table->unsignedBigInteger('chargeSeller')->change();
        });
    }
};
