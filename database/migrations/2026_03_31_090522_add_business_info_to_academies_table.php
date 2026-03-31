<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->string('representative_name')->nullable()->after('address')->comment('대표자명');
            $table->string('business_number')->nullable()->after('representative_name')->comment('사업자등록번호');
        });
    }

    public function down(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->dropColumn(['representative_name', 'business_number']);
        });
    }
};
