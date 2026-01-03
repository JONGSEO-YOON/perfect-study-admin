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
        Schema::table('test_sheets', function (Blueprint $table) {
            $table->json('print_layout')->nullable()->comment('
                시험지 출력 레이아웃 정보
                {
                    "pageLayoutModes": ["auto", "manual", ...],
                    "manualSplitPoints": [1, 3, 5],
                    "marginRights": [10, 20, 30]
                }
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sheets', function (Blueprint $table) {
            $table->dropColumn('print_layout');
        });
    }
};
