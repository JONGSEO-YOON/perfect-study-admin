<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('is_active')->comment('학원 로고 이미지');
            $table->string('favicon_path')->nullable()->after('logo_path')->comment('파비콘');
            $table->string('primary_color', 20)->nullable()->after('favicon_path')->comment('주요 색상 (예: #3B82F6)');
            $table->string('login_background_path')->nullable()->after('primary_color')->comment('로그인 배경 이미지');
            $table->text('login_welcome_message')->nullable()->after('login_background_path')->comment('로그인 환영 메시지');
        });
    }

    public function down(): void
    {
        Schema::table('academies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'favicon_path',
                'primary_color',
                'login_background_path',
                'login_welcome_message',
            ]);
        });
    }
};
