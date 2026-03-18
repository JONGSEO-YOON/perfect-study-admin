<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 문제/문제지 공유 권한 테이블
 *
 * 1. question_sharing: 기출 문제를 학원별로 공개/비공개 (최고관리자 설정)
 * 2. test_sheet_permissions: 문제지 단위 세분화 접근 권한
 */
return new class extends Migration
{
    public function up(): void
    {
        // 기출 문제 학원별 공유 설정
        // root_admin이 특정 기출 문제 세트를 학원별로 공개/비공개 설정
        Schema::create('exam_sharing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete()
                ->comment('공유 대상 학원');
            $table->string('source_type', 20)->comment('mock_exam 또는 school_exam');
            $table->boolean('is_allowed')->default(false)->comment('접근 허용 여부');
            // 세부 조건 (null이면 해당 source_type 전체)
            $table->unsignedSmallInteger('exam_year')->nullable()->comment('특정 년도');
            $table->unsignedTinyInteger('exam_month')->nullable()->comment('특정 월 (모의고사)');
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()
                ->comment('특정 학교 (학교기출)');
            $table->string('exam_type', 20)->nullable()->comment('midterm/final');
            $table->timestamps();

            $table->index(['academy_id', 'source_type']);
        });

        // 문제지 단위 접근 권한
        // 특정 문제지를 특정 학원에 공개/비공개
        Schema::create('test_sheet_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_sheet_id')->constrained('test_sheets')->cascadeOnDelete();
            $table->foreignId('academy_id')->constrained('academies')->cascadeOnDelete()
                ->comment('접근 허용 학원');
            $table->boolean('is_allowed')->default(true)->comment('접근 허용 여부');
            $table->timestamps();

            $table->unique(['test_sheet_id', 'academy_id']);
        });

        // questions 테이블에 공유 범위 필드 추가
        Schema::table('questions', function (Blueprint $table) {
            $table->string('share_scope', 20)->nullable()->after('is_public')
                ->comment('공유 범위: null=기본(is_public), all=모든학원, academy=소속학원만');
        });

        // test_sheets 테이블에 공유 범위 필드 추가
        Schema::table('test_sheets', function (Blueprint $table) {
            $table->string('share_scope', 20)->nullable()->after('source_type')
                ->comment('공유 범위: null=기본, all=모든학원, academy=소속학원만, restricted=권한 테이블 참조');
        });
    }

    public function down(): void
    {
        Schema::table('test_sheets', function (Blueprint $table) {
            $table->dropColumn('share_scope');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('share_scope');
        });

        Schema::dropIfExists('test_sheet_permissions');
        Schema::dropIfExists('exam_sharing_rules');
    }
};
