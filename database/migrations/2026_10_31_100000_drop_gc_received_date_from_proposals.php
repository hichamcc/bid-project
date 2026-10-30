<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GC receipt is tracked by the existing `responded` flag (colours the submitted
     * date green/red), so the separate date column is no longer used. Guarded because
     * only databases that ran an earlier version of the 2026_10_30 migration have it.
     */
    public function up(): void
    {
        if (Schema::hasColumn('proposals', 'gc_received_date')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('gc_received_date');
            });
        }
    }

    public function down(): void
    {
        // Nothing to restore: the column is no longer part of the schema.
    }
};
