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
        Schema::create('test_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('시험지 제목');
            $table->string('tag')->comment('시험지 태그');
            $table->string('status')->default('pending')->comment('시험지 태그');
            $table->foreignId('user_id')->nullable()->index();

            $table->enum('target_group', ['grade', 'level', 'classroom', 'student'])->default('grade')->comment('대상');
            $table->json('target_grades')->nullable()->comment('대상 학년들'); // [1, 2, 3]
            $table->json('target_levels')->nullable()->comment('대상 레벨들'); // ["A", "B"]
            $table->json('target_classrooms')->nullable()->comment('대상 교실/반들'); // [1, 2, 3] (classroom_ids)
            $table->json('target_students')->nullable()->comment('대상 학생들'); // [1, 2, 3] (student_ids)

            $table->json('scopes')->nullable()->comment('범위');
            // this is sample
            // $table->json('questions')->nullable()->comment('대상 학생들'); // [1, 2, 3] (student_ids)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sheets');
    }
};
