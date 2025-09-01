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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('student_id');
            $table->string('order_id')->unique()->comment('토스 결제 주문번호');
            $table->integer('amount');
            $table->string('payment_status')->default('pending'); // pending, paid, cancelled 등
            $table->string('payment_method')->nullable();
            $table->string('billing_name');
            $table->text('billing_memo')->nullable();
            $table->string('payment_key')->nullable()->comment('토스 결제 승인 후 받는 결제 키');
            $table->text('payment_log')->nullable()->comment('결제 로그 (영수증용)');
            $table->timestamp('approved_at')->nullable()->comment('결제 승인 시간');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
