<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DateDisplayWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now('Asia/Seoul');
        $currentDate = $now->format('Y년 m월 d일');
        $dayOfWeek = $now->locale('ko')->dayName;

        return [
            Stat::make('오늘 날짜', $currentDate)
                ->description($dayOfWeek)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }
}