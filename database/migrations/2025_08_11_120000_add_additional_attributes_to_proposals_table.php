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
            // Add job number
            $table->string('job_number')->nullable()->after('project_id');
            
            // Add responded field
            $table->enum('responded', ['yes', 'no'])->nullable()->after('submission_date');
            
            // Add first follow up fields
            $table->date('first_follow_up_date')->nullable()->after('responded');
            $table->enum('first_follow_up_respond', ['yes', 'no'])->nullable()->after('first_follow_up_date');
            
            // Add second follow up fields  
            $table->date('second_follow_up_date')->nullable()->after('first_follow_up_respond');
            $table->enum('second_follow_up_respond', ['yes', 'no'])->nullable()->after('second_follow_up_date');
            
            // Add third follow up fields
            $table->date('third_follow_up_date')->nullable()->after('second_follow_up_respond');
            $table->enum('third_follow_up_respond', ['yes', 'no'])->nullable()->after('third_follow_up_date');
            
            // Rename result to result_gc
            $table->renameColumn('result', 'result_gc');
            
            // Add result_art
            $table->enum('result_art', ['pending', 'win', 'loss'])->nullable()->after('result_gc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Drop the added columns in reverse order
            $table->dropColumn([
                'result_art',
                'third_follow_up_respond', 
                'third_follow_up_date',
                'second_follow_up_respond',
                'second_follow_up_date', 
                'first_follow_up_respond',
                'first_follow_up_date',
                'responded',
                'job_number'
            ]);
            
            // Rename result_gc back to result
            $table->renameColumn('result_gc', 'result');
        });
    }
};