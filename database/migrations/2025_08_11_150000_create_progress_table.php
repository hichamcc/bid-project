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
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->nullable();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->date('assigned_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->decimal('total_sqft', 10, 2)->nullable();
            $table->decimal('total_lnft', 10, 2)->nullable();
            $table->integer('total_sinks')->nullable();
            $table->integer('total_slabs')->nullable();
            $table->decimal('total_hours', 8, 2)->nullable();
            $table->timestamps();

            // Add indexes for commonly queried fields
            $table->index('project_id');
            $table->index('job_number');
            $table->index('assigned_date');
            $table->index('submission_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};