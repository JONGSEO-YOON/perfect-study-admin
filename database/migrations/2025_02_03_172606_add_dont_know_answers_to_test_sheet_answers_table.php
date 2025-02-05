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
        Schema::table('test_sheet_answers', function (Blueprint $table) {
            //
            $table->json('dont_know_answers')->nullable()->after('answers')
                ->comment('잘 모르겠어요로 응답한 문제 번호들');
        });
        Schema::table('wrong_answer_notes', function (Blueprint $table) {
            //
            $table->boolean('dont_know')->nullable()->default(false)->after('wrong_answer')
                ->comment('잘 모르겠어요로 응답한 문제 여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sheet_answers', function (Blueprint $table) {
            $table->dropColumn('dont_know_answers');
        });

        Schema::table('wrong_answer_notes', function (Blueprint $table) {
            $table->dropColumn('dont_know');
        });
    }
};
