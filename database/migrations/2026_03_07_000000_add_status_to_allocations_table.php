<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->enum('status', ['open', 'submitted'])->default('open')->after('job_type');
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
