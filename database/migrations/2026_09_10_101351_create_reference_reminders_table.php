<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('body');
            $table->timestamps();
            $table->index('created_at');
        });

        // The old single-blob notes table is replaced by the reminders list.
        Schema::dropIfExists('reference_notes');
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_reminders');

        Schema::create('reference_notes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }
};
