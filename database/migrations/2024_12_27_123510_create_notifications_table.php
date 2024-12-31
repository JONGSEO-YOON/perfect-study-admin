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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // 알림을 받는 사용자
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 알림 유형 (membership_approval, counseling_request, counseling_confirmation 등)
            $table->string('type');

            // 알림 제목
            $table->string('title');

            // 알림 내용
            $table->text('content')->nullable();

            // 알림과 관련된 데이터 (JSON 형태로 저장)
            $table->json('data')->nullable();

            // 읽음 여부
            $table->boolean('is_read')->default(false);

            // 읽은 시간
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // 인덱스 추가
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
