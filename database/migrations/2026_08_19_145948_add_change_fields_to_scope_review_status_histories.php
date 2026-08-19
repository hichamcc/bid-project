<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scope_review_status_histories', function (Blueprint $table) {
            // Generalise history rows into field-change records.
            // `field` = which attribute changed (decision, bid_stage, duration, notes...).
            // Existing rows have field NULL and represent a legacy decision change.
            $table->string('field')->nullable()->after('user_id');
            $table->text('old_value')->nullable()->after('field');
            $table->text('new_value')->nullable()->after('old_value');

            // `decision` was required before; make it nullable so non-decision
            // changes (e.g. a notes edit) can be recorded without it.
            $table->string('decision')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('scope_review_status_histories', function (Blueprint $table) {
            $table->dropColumn(['field', 'old_value', 'new_value']);
        });
    }
};
