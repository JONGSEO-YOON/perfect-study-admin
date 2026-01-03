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
        Schema::create('student_enrollment_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');

            // 액션 유형: enroll(입원), withdraw(퇴원), re-enroll(재입원)
            $table->enum('action', ['enroll', 'withdraw', 're_enroll'])
                ->comment('액션 유형: enroll(입원), withdraw(퇴원), re_enroll(재입원)');

            // 퇴원사유 (퇴원 시에만)
            $table->enum('reason', [
                'poor_performance',      // 성적부진
                'change_of_atmosphere',  // 분위기전환
                'teacher_mismatch',      // 선생님맞지않음
                'academy_atmosphere',    // 학원분위기안좋음
                'relocation',           // 이사
                'other',                // 기타
            ])->nullable()
                ->comment('퇴원사유');

            // 사유 상세
            $table->text('reason_detail')
                ->nullable()
                ->comment('사유 상세');

            // 담임 강사 ID
            $table->unsignedBigInteger('homeroom_teacher_id')
                ->nullable()
                ->comment('담임 강사 ID');

            $table->foreign('homeroom_teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('set null');

            // 액션 일자
            $table->date('action_date')
                ->comment('액션 일자 (입원일/퇴원일/재입원일)');

            // 메모
            $table->text('memo')
                ->nullable()
                ->comment('메모');

            // 처리한 관리자
            $table->unsignedBigInteger('processed_by')
                ->nullable()
                ->comment('처리한 관리자 ID');

            $table->foreign('processed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->timestamps();

            // 인덱스
            $table->index('action');
            $table->index('action_date');
            $table->index(['student_id', 'action_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollment_history');
    }
};
