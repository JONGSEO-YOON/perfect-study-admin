<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sharing_rules', function (Blueprint $table) {
            $table->string('exam_subject', 50)->nullable()->after('exam_type');
            $table->tinyInteger('exam_semester')->unsigned()->nullable()->after('exam_subject');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sharing_rules', function (Blueprint $table) {
            $table->dropColumn(['exam_subject', 'exam_semester']);
        });
    }
};
