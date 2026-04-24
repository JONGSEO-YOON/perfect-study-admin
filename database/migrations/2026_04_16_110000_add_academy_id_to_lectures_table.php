<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('lectures', 'academy_id')) {
            Schema::table('lectures', function (Blueprint $table) {
                $table->foreignId('academy_id')->nullable()->after('id')->constrained('academies')->nullOnDelete();
            });
        }

        // 기존 데이터: 등록자(user_id)의 academy_id로 채움, 없으면 1(퍼펙트 스터디)
        DB::statement("
            UPDATE lectures l
            JOIN users u ON l.user_id = u.id
            SET l.academy_id = u.academy_id
            WHERE l.academy_id IS NULL
        ");

        DB::statement("UPDATE lectures SET academy_id = 1 WHERE academy_id IS NULL");
    }

    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academy_id');
        });
    }
};
