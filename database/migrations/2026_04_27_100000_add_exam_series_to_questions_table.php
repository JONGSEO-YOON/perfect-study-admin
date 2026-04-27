<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // 모의고사 기출의 문제 계열
            // 가형, 나형, 이과, 문과, 공통, 선택(확률과통계), 선택(기하), 선택(미적분), 선택(이산수학)
            $table->string('exam_series', 50)->nullable()->after('exam_subject');
            $table->index(['source_type', 'exam_year', 'exam_series'], 'idx_mock_exam_series');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('idx_mock_exam_series');
            $table->dropColumn('exam_series');
        });
    }
};
