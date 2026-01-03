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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('materials')
                ->onDelete('cascade');
            $table->enum('type', ['book', 'folder']);
            $table->string('name');
            $table->string('image_path')
                ->nullable();
            $table->boolean('is_public')
                ->default(false);
            $table->timestamps();
            $table->softDeletes();

            // 인덱스 추가
            $table->index(['type', 'parent_id']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
