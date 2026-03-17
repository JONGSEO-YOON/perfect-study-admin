<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplementary_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->date('scheduled_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room')->nullable();
            $table->text('reason')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplementary_schedules');
    }
};
