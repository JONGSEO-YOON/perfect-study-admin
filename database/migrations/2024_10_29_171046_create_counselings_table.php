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
        Schema::create('counselings', function (Blueprint $table) {
            $table->id();

            $table->string('status');
            $table->text('request_message')->nullable();
            $table->foreignId('requester_id')
                ->nullable()
                ->constrained('users');
            $table->string('requester_type')->nullable();

            $table->string('type')->nullable();
            $table->string('target')->nullable();

            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained('classrooms');
            $table->foreignId('counselor_id')
                ->constrained('users');
            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students');

            // $table->foreignId('creator_id')
            //     ->constrained('users')
            //     ->nullable();

            $table->dateTime('counseled_at')->nullable();
            $table->date('planned_start_at')->nullable();
            $table->date('planned_end_at')->nullable();
            $table->text('title')->nullable();
            $table->text('content')->nullable();

            $table->boolean('confirmed')->default(false);
            $table->text('reply_message')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselings');
    }
};
