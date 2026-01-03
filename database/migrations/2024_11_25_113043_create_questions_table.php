<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        // 범위와 문제 유형을 모두 관리하는 테이블
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->enum('type', ['scope', 'question_type'])
                ->comment('scope: 범위(중1-1, 소인수분해 등), question_type: 문제 유형(약수와 배수의 성질 등)');
            $table->integer('depth')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 계층 구조를 관리하는 closure 테이블
        Schema::create('question_category_closure', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->integer('depth');

            $table->foreign('ancestor_id')
                ->references('id')
                ->on('question_categories')
                ->onDelete('cascade');
            $table->foreign('descendant_id')
                ->references('id')
                ->on('question_categories')
                ->onDelete('cascade');

            // ancestor와 descendant 쌍은 유니크해야 함
            $table->unique(['ancestor_id', 'descendant_id']);
        });

        // 문제 테이블 수정
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('question_type_id')
                ->nullable()
                ->constrained('question_categories')
                ->onDelete('cascade');

            $table->foreignId('parent_question_id')
                ->nullable()
                ->constrained('questions')
                ->onDelete('cascade');

            $table->foreignId('material_id')
                ->nullable()
                ->constrained('materials')
                ->onDelete('cascade');


            $table->integer('seq')->nullable();

            $table->enum('question_display_type', ['content', 'image'])
                ->default('content');
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('level')->default(1);

            // 객관식, 정수형 주관식
            $table->enum('answer_type', ['multiple_choice', 'integer'])
                ->default('multiple_choice');
            $table->integer('answer');
            $table->enum('explanation_display_type', ['content', 'image'])
                ->default('content');
            $table->enum('choices_display_type', ['seperate', 'in_question'])
                ->default('seperate');
            $table->text('explanation')->nullable();
            $table->string('explanation_image_path')->nullable();
            $table->string('explanation_video_url')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('number');
            $table->enum('display_type', ['content', 'image'])
                ->default('content');
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->unique(['question_id', 'number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_choices');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_category_closure');
        Schema::dropIfExists('question_categories');
    }
};
