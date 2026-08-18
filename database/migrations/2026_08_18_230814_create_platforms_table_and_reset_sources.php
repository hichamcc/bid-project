<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- New platforms table ---
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $platforms = [
            'BUILDING CONNECTED',
            'DODGEONE',
            'CONSTRUCT CONNECT/ISQFT',
            'SMARTBID',
            'PROCORE',
            'DIRECT EMAIL',
            'CLIENT PORTAL',
            'PANTERA',
            'THE BLUE BOOK',
            'PLANHUB',
        ];

        $now = now();
        $rows = [];
        foreach ($platforms as $i => $name) {
            $rows[] = ['name' => $name, 'is_active' => true, 'sort_order' => $i + 1, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('platforms')->insert($rows);

        // --- Reset the managed sources list to the real 3 sources ---
        // (Only the managed dropdown list; existing scope_reviews.source text is untouched.)
        DB::table('sources')->truncate();

        $sources = ['DISCOVERED', 'INVITED', 'FUTURE'];
        $sourceRows = [];
        foreach ($sources as $i => $name) {
            $sourceRows[] = ['name' => $name, 'is_active' => true, 'sort_order' => $i + 1, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('sources')->insert($sourceRows);
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
        // Sources are not restored on rollback (the original mixed list is not preserved).
    }
};
