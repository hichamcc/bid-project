<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Seed the initial source list.
        $sources = [
            'DISCOVERED',
            'INVITED',
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
        foreach ($sources as $i => $name) {
            $rows[] = [
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        \Illuminate\Support\Facades\DB::table('sources')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
