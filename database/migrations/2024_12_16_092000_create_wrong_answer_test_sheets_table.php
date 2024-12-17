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
        Schema::create('wrong_answer_test_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_test_sheet_id')
                ->constrained('test_sheets')
                ->onDelete('cascade');
            $table->foreignId('test_sheet_id')
                ->constrained('test_sheets')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('대상 학생');
            $table->integer('retry_count')
                ->default(1)
                ->comment('재시도 횟수 (1: 1차 오답, 2: 2차 오답)');
            $table->boolean('is_linked_to_original')
                ->default(true)
                ->comment('원본 테스트와 마감일이 연동되는지 여부');

            // 문제 정보를 JSON으로 저장
            $table->json('wrong_answer_questions')->comment('
                오답 문제와 새 문제의 매핑 정보
                [
                    {
                        "original_question_id": 1,
                        "new_question_id": 2,
                        "original_question_seq": 1,
                        "new_question_seq": 1
                    },
                    ...
                ]
            ');

            $table->timestamps();

            // 동일한 원본 테스트-학생-재시도횟수 조합은 unique해야 함
            $table->unique(
                ['original_test_sheet_id', 'user_id', 'retry_count'],
                'wat_unique_test_user_retry'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrong_answer_test_sheets');
    }
};
