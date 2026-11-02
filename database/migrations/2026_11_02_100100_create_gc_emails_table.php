<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Emails shown on the GC page. For now they come from the follow-up attachments
     * uploaded on proposals (source = upload); a future Outlook sync can add rows
     * with source = outlook and gc_id set directly.
     */
    public function up(): void
    {
        Schema::create('gc_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gc_id')->nullable()->constrained('gcs')->nullOnDelete();
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->cascadeOnDelete();
            $table->unsignedTinyInteger('follow_up')->nullable();
            $table->string('source', 20)->default('upload');
            $table->string('file_path')->nullable();
            $table->string('file_type', 10)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->text('to_name')->nullable();
            $table->text('to_email')->nullable();
            $table->string('subject', 500)->nullable();
            $table->text('body_preview')->nullable();
            $table->json('attachments')->nullable();
            $table->string('parse_error')->nullable();
            $table->timestamps();

            $table->unique(['proposal_id', 'follow_up']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gc_emails');
    }
};
