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
            $table->dateTime('delivered_at')->nullable()->after('claimedByBuyer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_trustap_transactions', function (Blueprint $table) {
            $table->dropColumn(['delivered_at']);
        });
    }
};
