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
        Schema::create('toss_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id');
            $table->string('order_id')->unique()->comment('토스 결제 주문번호');
            $table->json('payment_info')->comment('결제 정보 (method, amount, orderName 등)');
            $table->text('payment_log')->nullable()->comment('결제 로그 (영수증용)');
            $table->string('success_url')->comment('결제 성공 시 리다이렉션 URL');
            $table->string('fail_url')->comment('결제 실패 시 리다이렉션 URL');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->comment('결제 상태');
            $table->string('payment_key')->nullable()->comment('토스 결제 승인 후 받는 결제 키');
            $table->timestamp('approved_at')->nullable()->comment('결제 승인 시간');
            $table->timestamps();

            // 인덱스 추가
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toss_payments');
    }
};
