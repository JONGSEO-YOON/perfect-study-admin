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
        //
        Schema::create('wrong_answer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('학생 ID');

            $table->json('question')->comment('문제 정보 (JSON)');
            $table->integer('wrong_answer')->nullable()->comment('학생이 작성한 오답');
            $table->timestamps();

            // index by student_id
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('wrong_answer_notes');
    }
};
