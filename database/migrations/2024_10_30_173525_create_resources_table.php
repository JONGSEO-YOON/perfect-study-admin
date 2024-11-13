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
        Schema::create('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('resource_sub_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resource_category_id')
                ->nullable()
                ->constrained('resource_categories')
                ->onDelete('cascade');

            // $table->foreignId('user_id')
            //     ->constrained('users')
            //     ->onDelete('cascade');

            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('content');
            $table->json('attachments')->nullable();

            $table->foreignId('author_id')->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('resource_sub_category_id')
                ->nullable()
                ->constrained('resource_sub_categories')
                ->onDelete('cascade');

            $table->timestamp('pinned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('resource_sub_categories');
        Schema::dropIfExists('resource_categories');
    }
};
