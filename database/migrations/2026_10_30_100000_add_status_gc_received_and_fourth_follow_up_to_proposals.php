<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // open | closed | inactive | on_hold. No default: existing proposals stay empty
            // until someone sets them; new ones get "Open" preselected in the form.
            $table->string('status')->nullable()->after('job_number');

            // Date the GC confirmed they received the proposal (separate from submission_date).
            $table->date('gc_received_date')->nullable()->after('responded');

            $table->date('fourth_follow_up_date')->nullable()->after('third_follow_up_attachment');
            $table->enum('fourth_follow_up_respond', ['yes', 'no'])->nullable()->after('fourth_follow_up_date');
            $table->string('fourth_follow_up_attachment')->nullable()->after('fourth_follow_up_respond');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'gc_received_date',
                'fourth_follow_up_date',
                'fourth_follow_up_respond',
                'fourth_follow_up_attachment',
            ]);
        });
    }
};
