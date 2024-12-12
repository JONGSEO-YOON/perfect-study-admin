<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('test_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('시험지 제목');
            $table->json('tags')->nullable()->comment('시험지 태그들');
            $table->json('scopes')->nullable()->comment('시험지 태그들');
            $table->string('status')->default('pending')->comment('시험지 상태');
            $table->foreignId('user_id')->nullable()->index();
            $table->enum('target_group', ['grade', 'level', 'classroom', 'student'])->default('grade')->comment('대상');
            $table->json('target_grades')->nullable()->comment('대상 학년들'); // [1, 2, 3]
            $table->json('target_levels')->nullable()->comment('대상 레벨들'); // ["A", "B"]
            $table->json('target_classrooms')->nullable()->comment('대상 교실/반들'); // [1, 2, 3] (classroom_ids)
            $table->json('target_students')->nullable()->comment('대상 학생들'); // [1, 2, 3] (student_ids)
            $table->boolean('is_auto')->default(true)->comment('자동 출제 여부');

            $table->boolean('use_score_table')->default(true)->comment('배점표 사용 여부');
            $table->json('score_table')->nullable()->comment('배점표');
            $table->json('parsed_score_table')->nullable()->comment('배점표');

            $table->timestamp('start_date')->nullable()->comment('출제일');
            $table->timestamp('end_date')->nullable()->comment('마감일');
            $table->string('template')->default('default')->comment('템플릿');
            $table->string('split')->default('default')->comment('문제 분할 방식');
            $table->string('title')->comment('제목');
            $table->string('sub_title')->comment('부제목');
            $table->json('questions')->nullable()->comment('문제 목록');
            $table->string('temp_data_id')->nullable();
            $table->json('report')->nullable()->comment('시험 결과 리포트');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sheets');
    }
};
