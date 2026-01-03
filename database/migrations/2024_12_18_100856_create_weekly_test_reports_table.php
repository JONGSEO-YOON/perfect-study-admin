<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weekly_test_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('week');
            $table->string('type');
            $table->json('report');
            $table->timestamps();

            // 복합 인덱스 추가 - 학생별, 연도별, 주별 빠른 조회를 위해
            $table->index(['student_id', 'year', 'week'], 'wtr_student_year_week_idx');
            // 반별, 연도별, 주별 빠른 조회를 위해
            $table->index(['classroom_id', 'year', 'week'], 'wtr_classroom_year_week_idx');
            // 중복 데이터 방지를 위한 유니크 제약
            $table->unique(['student_id', 'classroom_id', 'year', 'week', 'type'], 'wtr_unique_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_test_reports');
    }
};
