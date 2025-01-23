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
        Schema::table('counselings', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->dropForeign(['counselor_id']);

            $table->foreign('requester_id')
                ->references('id')
                ->on('users')
                ->nullable()
                ->cascadeOnDelete();

            $table->foreign('counselor_id')
                ->references('id')
                ->on('users')
                ->nullable()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselings', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->dropForeign(['counselor_id']);

            $table->foreign('requester_id')
                ->references('id')
                ->on('users')
                ->nullable();

            $table->foreign('counselor_id')
                ->references('id')
                ->on('users')
                ->nullable()
                ->cascadeOnDelete();
        });
    }
};
