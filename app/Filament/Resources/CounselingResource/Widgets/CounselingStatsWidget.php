<?php

namespace App\Filament\Resources\CounselingResource\Widgets;

use App\Models\Counseling;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CounselingStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = Counseling::count();
        $requested = Counseling::where('status', '상담 요청')->count();
        $inProgress = Counseling::where('status', '상담 진행')->count();
        $completed = Counseling::where('status', '상담 완료')->count();

        return [
            Stat::make('전체 상담', $total . '건')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),
            Stat::make('상담 요청', $requested . '건')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color($requested > 0 ? 'warning' : 'gray'),
            Stat::make('상담 진행', $inProgress . '건')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($inProgress > 0 ? 'info' : 'gray'),
            Stat::make('상담 완료', $completed . '건')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
