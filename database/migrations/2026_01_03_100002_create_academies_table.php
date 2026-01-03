<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academies', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->comment('학원명');
            $table->string('code')->unique()->nullable()->comment('학원 코드');
            
            $table->string('business_name')->nullable()->comment('상호명 (사업자)');
            $table->string('business_number', 20)->nullable()->comment('사업자등록번호');
            $table->string('representative_name', 100)->nullable()->comment('대표자명');
            $table->text('business_address')->nullable()->comment('사업장 주소');
            $table->string('business_phone', 20)->nullable()->comment('사업장 전화번호');
            
            $table->string('contact_phone', 20)->nullable()->comment('연락처');
            $table->string('contact_email')->nullable()->comment('이메일');
            
            $table->boolean('is_active')->default(true)->comment('활성 상태');
            
            $table->text('memo')->nullable()->comment('메모');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academies');
    }
};
