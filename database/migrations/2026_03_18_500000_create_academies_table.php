<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * 멀티 학원(Multi-tenancy) 기반 마이그레이션
 *
 * 1. academies 테이블 생성
 * 2. 핵심 테이블에 academy_id 컬럼 추가 (nullable, 기존 데이터 보존)
 * 3. 기존 데이터를 academy_id = 1 (퍼펙트 스터디)로 배정
 * 4. users 테이블에 academy_id 추가
 *
 * 롤백: php artisan migrate:rollback --step=1 --force
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. academies 테이블 생성
        if (Schema::hasTable('academies')) {
            Schema::drop('academies');
        }
        Schema::create('academies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('학원 이름');
            $table->string('slug')->unique()->comment('URL slug');
            $table->boolean('is_active')->default(true)->comment('활성 여부');
            $table->string('toss_client_key')->nullable()->comment('토스 클라이언트 키');
            $table->string('toss_secret_key')->nullable()->comment('토스 시크릿 키');
            $table->string('toss_customer_key')->nullable()->comment('토스 고객 키');
            $table->string('phone')->nullable()->comment('학원 전화번호');
            $table->string('address')->nullable()->comment('학원 주소');
            $table->json('settings')->nullable()->comment('학원별 설정 (JSON)');
            $table->timestamps();
        });

        // 2. 기본 학원 (퍼펙트 스터디) 데이터 삽입
        DB::table('academies')->insert([
            'id' => 1,
            'name' => '퍼펙트 스터디',
            'slug' => 'perfect-study',
            'is_active' => true,
            'toss_client_key' => config('services.toss.client_key'),
            'toss_secret_key' => config('services.toss.secret_key'),
            'toss_customer_key' => config('services.toss.customer_key'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. 핵심 테이블에 academy_id 추가 + 기존 데이터 배정
        $tables = [
            'users',
            'teachers',
            'students',
            'counselors',
            'classrooms',
            'payments',
            'test_sheets',
            'questions',
            'materials',
            'notices',
            'student_notices',
            'counselings',
            'attendance_logs',
            'supplementary_schedules',
            'resources',
            'lectures',
            'payment_schedules',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'academy_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('academy_id')->nullable()->after('id')
                        ->constrained('academies')->nullOnDelete();
                    $table->index('academy_id');
                });

                // 기존 데이터를 퍼펙트 스터디(id=1)에 배정
                DB::table($tableName)->whereNull('academy_id')->update(['academy_id' => 1]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'payment_schedules',
            'lectures',
            'resources',
            'supplementary_schedules',
            'attendance_logs',
            'counselings',
            'student_notices',
            'notices',
            'materials',
            'questions',
            'test_sheets',
            'payments',
            'classrooms',
            'counselors',
            'students',
            'teachers',
            'users',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'academy_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['academy_id']);
                    $table->dropIndex(['academy_id']);
                    $table->dropColumn('academy_id');
                });
            }
        }

        Schema::dropIfExists('academies');
    }
};
