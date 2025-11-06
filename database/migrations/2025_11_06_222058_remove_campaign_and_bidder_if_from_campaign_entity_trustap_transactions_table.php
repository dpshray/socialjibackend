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
        Schema::table('campaign_entity_trustap_transactions', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['bidder_id']);
            $table->dropColumn(['campaign_id', 'bidder_id']);});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_entity_trustap_transactions', function (Blueprint $table) {
            //
        });
    }
};
