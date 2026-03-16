<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add status to pivot table
        Schema::table('allocation_user', function (Blueprint $table) {
            $table->enum('status', ['open', 'submitted'])->default('open')->after('user_id');
        });

        // Migrate existing allocation status to each estimator's pivot row
        DB::statement("
            UPDATE allocation_user au
            JOIN allocations a ON au.allocation_id = a.id
            SET au.status = a.status
        ");

        // Drop status from allocations table
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->enum('status', ['open', 'submitted'])->default('open')->after('job_type');
        });

        Schema::table('allocation_user', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
