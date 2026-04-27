<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 학교 기출 배점에 소수점(0.5점, 2.5점 등) 허용
        DB::statement('ALTER TABLE questions MODIFY exam_score DECIMAL(5,2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE questions MODIFY exam_score TINYINT UNSIGNED NULL');
    }
};
