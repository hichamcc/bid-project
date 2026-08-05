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
        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->string('reason_to_ignore')->nullable()->after('notes');
            $table->string('bid_stage')->nullable()->after('reason_to_ignore');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scope_reviews', function (Blueprint $table) {
            $table->dropColumn(['reason_to_ignore', 'bid_stage']);
        });
    }
};
