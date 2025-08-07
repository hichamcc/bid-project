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
        Schema::table('proposals', function (Blueprint $table) {
            $table->date('submission_date')->nullable()->after('project_id');
            $table->decimal('price_original', 12, 2)->nullable()->after('submission_date');
            $table->decimal('price_ve', 12, 2)->nullable()->after('price_original');
            $table->enum('result', ['win', 'loss'])->nullable()->after('price_ve');
            $table->decimal('gc_price', 12, 2)->nullable()->after('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'submission_date',
                'price_original', 
                'price_ve',
                'result',
                'gc_price'
            ]);
        });
    }
};