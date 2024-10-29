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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('phone_father')->nullable();
            $table->string('phone_mother')->nullable();

            $table->boolean('sms_agree')->default(true);
            $table->string('sms_targets')->nullable()->default('[]');

            $table->enum('cash_receipt_type', [
                '개인',
                '사업자',
            ])->nullable();

            $table->string('cash_receipt_no')->nullable();

            $table->date('initially_attended_at')->nullable();

            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->unsignedBigInteger('grade_system_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('set null');
            $table->foreign('grade_system_id')->references('id')->on('grade_systems')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
