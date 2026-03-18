<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 기출 문제은행 확장 마이그레이션
 *
 * 기존 questions 테이블에 모의고사/학교 기출 관련 컬럼을 추가합니다.
 * 모든 새 컬럼은 nullable이므로 기존 데이터에 영향 없음.
 *
 * 롤백: php artisan migrate:rollback --step=1
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // 문제 출처 구분 (null=기존 문제, mock_exam=모의고사, school_exam=학교기출)
            $table->string('source_type')->nullable()->after('metadata')
                ->comment('문제 출처: null=일반, mock_exam=모의고사, school_exam=학교기출');

            // === 모의고사 기출 전용 필드 ===
            $table->unsignedSmallInteger('exam_year')->nullable()->after('source_type')
                ->comment('모의고사/학교기출 년도 (예: 2024)');
            $table->unsignedTinyInteger('exam_month')->nullable()->after('exam_year')
                ->comment('모의고사 월 (예: 6, 9, 11)');
            $table->string('exam_grade', 20)->nullable()->after('exam_month')
                ->comment('학년 (예: 고1, 고2, 고3)');
            $table->string('exam_subject', 50)->nullable()->after('exam_grade')
                ->comment('과목 (대수, 미적분, 확통, 기하, 미적분2, 공통수학 등)');
            $table->unsignedTinyInteger('exam_score')->nullable()->after('exam_subject')
                ->comment('배점 (2점, 3점, 4점)');
            $table->unsignedSmallInteger('exam_question_number')->nullable()->after('exam_score')
                ->comment('원본 문제 번호 (모의고사 1~30번, 학교시험 번호)');

            // === 학교 기출 전용 필드 ===
            $table->foreignId('school_id')->nullable()->after('exam_question_number')
                ->constrained('schools')->nullOnDelete()
                ->comment('학교 ID (schools 테이블 참조)');
            $table->unsignedTinyInteger('exam_semester')->nullable()->after('school_id')
                ->comment('학기 (1=1학기, 2=2학기)');
            $table->string('exam_type', 20)->nullable()->after('exam_semester')
                ->comment('시험 유형 (midterm=중간고사, final=기말고사)');

            // === 인덱스 ===
            $table->index('source_type');
            $table->index(['source_type', 'exam_year', 'exam_month'], 'idx_mock_exam');
            $table->index(['source_type', 'school_id', 'exam_year', 'exam_semester'], 'idx_school_exam');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // 외래키 먼저 제거
            $table->dropForeign(['school_id']);

            // 인덱스 제거
            $table->dropIndex('idx_mock_exam');
            $table->dropIndex('idx_school_exam');
            $table->dropIndex(['source_type']);

            // 컬럼 제거
            $table->dropColumn([
                'source_type',
                'exam_year',
                'exam_month',
                'exam_grade',
                'exam_subject',
                'exam_score',
                'exam_question_number',
                'school_id',
                'exam_semester',
                'exam_type',
            ]);
        });
    }
};
