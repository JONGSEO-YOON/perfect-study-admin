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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['attendance_date', 'check_in_time', 'check_out_time']);
            $table->string('type')->after('student_id');
            $table->boolean('is_late')->default(false)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_late']);
            $table->date('attendance_date')->after('student_id');
            $table->time('check_in_time')->nullable()->after('attendance_date');
            $table->time('check_out_time')->nullable()->after('check_in_time');
        });
    }
};
