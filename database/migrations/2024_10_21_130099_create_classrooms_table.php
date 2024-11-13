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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->text('remark')->nullable();
            $table->json('attachments')->nullable();

            $table->json('target_grades')->nullable()->comment('대상 학년들');
            $table->string('target_level')->comment('대상 레벨');

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->json('timetable')->nullable();

            $table->timestamps();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
