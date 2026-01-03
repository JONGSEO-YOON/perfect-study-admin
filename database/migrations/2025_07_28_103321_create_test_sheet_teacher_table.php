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
        Schema::create('test_sheet_teacher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_sheet_id')->comment('시험지 ID');
            $table->unsignedBigInteger('teacher_id')->comment('교사 ID');
            $table->timestamps();

            // 복합 유니크 인덱스 (중복 방지)
            $table->unique(['test_sheet_id', 'teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sheet_teacher');
    }
};
