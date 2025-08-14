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
            $table->string('first_follow_up_attachment')->nullable()->after('first_follow_up_respond');
            $table->string('second_follow_up_attachment')->nullable()->after('second_follow_up_respond');
            $table->string('third_follow_up_attachment')->nullable()->after('third_follow_up_respond');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'first_follow_up_attachment',
                'second_follow_up_attachment', 
                'third_follow_up_attachment'
            ]);
        });
    }
};
