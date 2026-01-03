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
        Schema::table('students', function (Blueprint $table) {
            // 학생 상태: enrolled(재원), pending(승인예정), withdrawn(퇴원)
            $table->enum('status', ['enrolled', 'pending', 'withdrawn'])
                ->default('enrolled')
                ->after('school_id')
                ->comment('학생 상태: enrolled(재원), pending(승인예정), withdrawn(퇴원)');

            // 퇴원일자
            $table->date('withdrawn_at')
                ->nullable()
                ->after('status')
                ->comment('퇴원일자');

            // 퇴원사유
            $table->enum('withdrawal_reason', [
                'poor_performance',      // 성적부진
                'change_of_atmosphere',  // 분위기전환
                'teacher_mismatch',      // 선생님맞지않음
                'academy_atmosphere',    // 학원분위기안좋음
                'relocation',           // 이사
                'other',                // 기타
            ])->nullable()
                ->after('withdrawn_at')
                ->comment('퇴원사유');

            // 퇴원사유 상세 (기타 선택 시)
            $table->text('withdrawal_reason_detail')
                ->nullable()
                ->after('withdrawal_reason')
                ->comment('퇴원사유 상세 (기타 선택 시)');

            // 담임 강사 ID
            $table->unsignedBigInteger('homeroom_teacher_id')
                ->nullable()
                ->after('withdrawal_reason_detail')
                ->comment('담임 강사 ID');

            $table->foreign('homeroom_teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('set null');

            // 인덱스 추가
            $table->index('status');
            $table->index('withdrawn_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['homeroom_teacher_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['withdrawn_at']);
            $table->dropColumn([
                'status',
                'withdrawn_at',
                'withdrawal_reason',
                'withdrawal_reason_detail',
                'homeroom_teacher_id',
            ]);
        });
    }
};
