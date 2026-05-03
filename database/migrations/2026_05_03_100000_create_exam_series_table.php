<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_series', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $defaults = ['가형', '나형', '이과', '문과', '공통', '확률과통계', '기하', '미적분', '이산수학'];
        $now = now();
        foreach ($defaults as $i => $name) {
            DB::table('exam_series')->insert([
                'name' => $name,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_series');
    }
};
