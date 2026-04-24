<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_status_histories', function (Blueprint $table) {
            // 이벤트 유형 (status_change, created, approved, approval_revoked, updated, deleted, classroom_assigned, classroom_removed)
            $table->string('event_type')->default('status_change')->after('changed_by');

            // 기존에는 from_status, to_status가 필수였으나, 정보 수정/삭제/반 배정 등은 status 전이가 없으므로 nullable로 변경
            $table->string('from_status')->nullable()->change();
            $table->string('to_status')->nullable()->change();

            $table->index('event_type');
        });

        // 기존 데이터는 status_change로 분류
        DB::table('student_status_histories')->update(['event_type' => 'status_change']);
    }

    public function down(): void
    {
        Schema::table('student_status_histories', function (Blueprint $table) {
            $table->dropIndex(['event_type']);
            $table->dropColumn('event_type');
        });
    }
};
