<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 기출 문제지 확장 마이그레이션
 *
 * 기존 test_sheets 테이블에 기출 문제지 관련 컬럼을 추가합니다.
 * 모든 새 컬럼은 nullable이므로 기존 데이터에 영향 없음.
 *
 * 롤백: php artisan migrate:rollback --step=1
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sheets', function (Blueprint $table) {
            // 문제지 출처 구분 (null=기존 문제지, mock_exam=모의고사기출, school_exam=학교기출)
            $table->string('source_type')->nullable()->after('print_layout')
                ->comment('문제지 출처: null=일반, mock_exam=모의고사, school_exam=학교기출');

            // 문제지 생성 방식 (number=문제번호로 추가, category=단원으로 추가)
            $table->string('creation_method', 20)->nullable()->after('source_type')
                ->comment('생성 방식: number=문제번호, category=단원');

            // === 모의고사 기출 문제지 필드 ===
            $table->json('exam_years')->nullable()->after('creation_method')
                ->comment('선택된 년도 (JSON 배열, 단원 방식 시 복수 선택 가능)');
            $table->json('exam_months')->nullable()->after('exam_years')
                ->comment('선택된 월 (JSON 배열)');
            $table->json('exam_grades')->nullable()->after('exam_months')
                ->comment('선택된 학년 (JSON 배열)');
            $table->json('exam_subjects')->nullable()->after('exam_grades')
                ->comment('선택된 과목 (JSON 배열)');
            $table->json('exam_scores')->nullable()->after('exam_subjects')
                ->comment('선택된 점수/배점 (JSON 배열)');

            // === 학교 기출 문제지 필드 ===
            $table->foreignId('school_id')->nullable()->after('exam_scores')
                ->constrained('schools')->nullOnDelete()
                ->comment('학교 ID');
            $table->json('exam_semesters')->nullable()->after('school_id')
                ->comment('선택된 학기 (JSON 배열)');
            $table->json('exam_types')->nullable()->after('exam_semesters')
                ->comment('시험 유형 (JSON 배열: midterm, final)');
        });
    }

    public function down(): void
    {
        Schema::table('test_sheets', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn([
                'source_type',
                'creation_method',
                'exam_years',
                'exam_months',
                'exam_grades',
                'exam_subjects',
                'exam_scores',
                'school_id',
                'exam_semesters',
                'exam_types',
            ]);
        });
    }
};
