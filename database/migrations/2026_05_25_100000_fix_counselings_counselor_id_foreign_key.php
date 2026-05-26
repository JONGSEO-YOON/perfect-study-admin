<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 상담관리 counselor_id 외래키를 users → teachers 로 정정.
     * 코드는 counselor_id 를 teacher_id 로 일관되게 사용하지만,
     * 마이그레이션에서 잘못 users(id) 로 constrained 해 두어 새 상담 저장 시 FK 위반 → 500 발생.
     */
    public function up(): void
    {
        if (!Schema::hasTable('counselings')) {
            return;
        }

        // 기존 FK 가 users(id) 를 가리키고 있으면 drop 후 teachers(id) 로 다시 연결
        try {
            Schema::table('counselings', function (Blueprint $table) {
                $table->dropForeign('counselings_counselor_id_foreign');
            });
        } catch (\Throwable $e) {
            // 이미 drop 됐거나 다른 이름이면 무시
        }

        Schema::table('counselings', function (Blueprint $table) {
            $table->foreign('counselor_id')
                ->references('id')->on('teachers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('counselings')) {
            return;
        }

        try {
            Schema::table('counselings', function (Blueprint $table) {
                $table->dropForeign('counselings_counselor_id_foreign');
            });
        } catch (\Throwable $e) {
            // 무시
        }

        Schema::table('counselings', function (Blueprint $table) {
            $table->foreign('counselor_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }
};
