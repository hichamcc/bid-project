<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reason_to_ignore', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $reasons = [
            'NOT IN OUR SCOPE',
            'BY MILLWORK VENDOR',
            'OWNER FURNISHED-CONTRACTOR INSTALLED',
            'OWNER FURNISHED-OWNER INSTALLED',
            'NOT IN CONTRACT (NIC)',
            'NOT OUR MATERIAL',
            'MU ONLY',
        ];

        $now = now();
        $rows = [];
        foreach ($reasons as $i => $name) {
            $rows[] = [
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('reason_to_ignore')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('reason_to_ignore');
    }
};
