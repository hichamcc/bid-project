<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->boolean('intention_to_bid_email_sent')->default(false)->after('uploaded_in_oh');
            $table->boolean('not_bidding_email_sent')->default(false)->after('intention_to_bid_email_sent');
        });
    }

    public function down(): void
    {
        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->dropColumn(['intention_to_bid_email_sent', 'not_bidding_email_sent']);
        });
    }
};
