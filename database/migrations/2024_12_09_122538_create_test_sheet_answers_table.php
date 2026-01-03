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
        Schema::create('test_sheet_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_sheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('answers');
            $table->enum('status', [
                'pending',
                'completed',
            ])->default('pending');
            $table->integer('time')->default(0);

            $table->integer('correct_count')->nullable();
            $table->json('correct_count_report')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sheet_answers');
    }
};
