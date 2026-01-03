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
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 공개 여부
            $table->boolean('display')->default(false);

            // 공개 기간
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expired_at')->nullable();

            // 공개 대상 정보 (복수 선택 가능)
            $table->enum('target_group', ['grade', 'level', 'classroom', 'student'])->default('grade')->comment('대상');
            $table->json('target_grades')->nullable()->comment('대상 학년들'); // [1, 2, 3]
            $table->json('target_levels')->nullable()->comment('대상 레벨들'); // ["A", "B"]
            $table->json('target_classrooms')->nullable()->comment('대상 교실/반들'); // [1, 2, 3] (classroom_ids)
            $table->json('target_students')->nullable()->comment('대상 학생들'); // [1, 2, 3] (student_ids)

            // 강의 정보
            $table->string('title')->comment('강의 제목');
            $table->text('description')->nullable()->comment('강의 설명');
            $table->json('lecture_info')->nullable()->comment('강의 세부 정보 (교재, 난이도, 강의 자료 등)');
            $table->json('attachments')->nullable()->comment('첨부 파일');
            $table->json('scopes')->nullable()->comment('범위');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};
