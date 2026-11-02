<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gc_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gc_id')->constrained('gcs')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('role')->nullable();
            $table->string('office_number', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gc_contacts');
    }
};
