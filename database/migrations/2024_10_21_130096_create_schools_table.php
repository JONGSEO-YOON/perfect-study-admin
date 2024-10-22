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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('administrative_code')->comment('행정표준코드');
            $table->string('name')->comment('학교명');
            $table->string('school_type')->comment('학교종류명');
            $table->string('province')->comment('시도명');
            $table->string('postal_code')->comment('우편번호');
            $table->string('address')->comment('주소');
            $table->string('phone_number')->nullable()->comment('전화번호');
            $table->string('website_url')->nullable()->comment('홈페이지주소');
            $table->timestamps();

            // 인덱스 추가
            $table->index('administrative_code');
            $table->index('name');
            $table->index('school_type');
            $table->index('province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
