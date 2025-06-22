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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gc')->nullable(); // General Contractor
            $table->text('scope')->nullable();
            $table->date('assigned_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->nullable();
            $table->string('rfi')->nullable(); // Request for Information
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('project_information')->nullable();
            $table->string('web_link')->nullable();
            $table->timestamps();

            $table->index(['status', 'assigned_date']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};