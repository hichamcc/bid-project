<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LOG INS — platform credentials. Password is stored encrypted (model cast).
        Schema::create('reference_logins', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('email')->nullable();
            $table->text('password')->nullable(); // encrypted at rest
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('sort_order');
        });

        // SKIPPED GC — GC name + a status + optional note.
        Schema::create('reference_skipped_gcs', function (Blueprint $table) {
            $table->id();
            $table->string('gc_name');
            $table->string('status')->default('SKIP'); // SKIP / PROCEED / ON HOLD / NO MU PROJECTS
            $table->text('note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('sort_order');
        });

        // INFOS — material/vendor reference rows.
        Schema::create('reference_infos', function (Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->text('description')->nullable();
            $table->string('status')->nullable(); // Yes / No / OK / free text
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('sort_order');
        });

        // Free-text reference notes (key/value), e.g. the Infos "Reminders" panel.
        Schema::create('reference_notes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_notes');
        Schema::dropIfExists('reference_infos');
        Schema::dropIfExists('reference_skipped_gcs');
        Schema::dropIfExists('reference_logins');
    }
};
