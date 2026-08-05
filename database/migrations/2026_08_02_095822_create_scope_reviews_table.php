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
        Schema::create('scope_reviews', function (Blueprint $table) {
            $table->id();

            // Admin intake fields
            $table->date('entry_date');
            $table->string('source')->nullable();
            $table->string('platform')->nullable();
            $table->string('project_name');
            $table->date('due_date')->nullable();
            $table->string('project_link', 2048)->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('assigned_estimator_id')->nullable()->constrained('users')->onDelete('set null');

            // Estimator review fields
            $table->string('project_type')->nullable(); // MU / NON_MU
            $table->string('decision')->nullable(); // approved / rfi_requested / not_in_scope
            $table->string('duration')->nullable();
            $table->text('estimator_notes')->nullable();
            $table->boolean('uploaded_in_oh')->default(false);
            $table->timestamp('reviewed_at')->nullable();

            // Stamped once decision = approved
            $table->string('project_number')->nullable();

            // Set once admin converts this approved entry into a real Allocation/Project
            $table->foreignId('allocation_id')->nullable()->constrained('allocations')->onDelete('set null');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_reviews');
    }
};
