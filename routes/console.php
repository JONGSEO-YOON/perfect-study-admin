<?php

use App\Console\Commands\CleanupOldData;
use App\Console\Commands\SendScheduledPayments;
use App\Console\Commands\UpdateTestSheetStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command(UpdateTestSheetStatus::class)->everyMinute();
Schedule::command(SendScheduledPayments::class)->dailyAt('09:00');

// 매일 새벽 4시: 4개월(123일) 이상 된 시험지/오답노트/성적표 데이터 정리
Schedule::command(CleanupOldData::class)->dailyAt('04:00');
