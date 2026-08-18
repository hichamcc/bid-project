<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bid_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $stages = [
            'AWARD / EXECUTION',
            'CONCEPTUAL / FEASIBILITY',
            'DESIGN DEVELOPMENT',
            'GMP / BUYOUT',
            'NOT IN OUR SCOPE',
        ];

        $now = now();
        $rows = [];
        foreach ($stages as $i => $name) {
            $rows[] = [
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        \Illuminate\Support\Facades\DB::table('bid_stages')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_stages');
    }
};
