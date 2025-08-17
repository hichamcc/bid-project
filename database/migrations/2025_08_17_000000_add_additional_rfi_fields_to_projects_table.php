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
        Schema::table('projects', function (Blueprint $table) {
            // Second RFI dates and attachment
            $table->date('second_rfi_request_date')->nullable()->after('rfi_due_date');
            $table->date('second_rfi_due_date')->nullable()->after('second_rfi_request_date');
            $table->string('second_rfi_attachment')->nullable()->after('second_rfi_due_date');
            
            // Third RFI dates and attachment
            $table->date('third_rfi_request_date')->nullable()->after('second_rfi_attachment');
            $table->date('third_rfi_due_date')->nullable()->after('third_rfi_request_date');
            $table->string('third_rfi_attachment')->nullable()->after('third_rfi_due_date');
            
            // First RFI attachment (for existing first RFI)
            $table->string('first_rfi_attachment')->nullable()->after('rfi_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'second_rfi_request_date',
                'second_rfi_due_date',
                'second_rfi_attachment',
                'third_rfi_request_date',
                'third_rfi_due_date',
                'third_rfi_attachment',
                'first_rfi_attachment'
            ]);
        });
    }
};