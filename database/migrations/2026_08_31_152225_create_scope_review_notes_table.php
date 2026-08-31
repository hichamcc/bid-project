<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_review_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scope_review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            // 'admin' or 'estimator' — keeps the existing note split.
            $table->string('context')->default('admin');
            $table->text('body');
            $table->timestamps();

            $table->index(['scope_review_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_review_notes');
    }
};
