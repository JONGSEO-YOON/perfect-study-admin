<?php

namespace App\Filament\Widgets;

use App\Models\Counseling;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CounselingWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getHeading(): string
    {
        return '상담 현황';
    }

    protected function getStats(): array
    {
        $now = Carbon::now('Asia/Seoul');

        $requested = Counseling::where('status', '상담 요청')->count();
        $inProgress = Counseling::where('status', '상담 진행')->count();

        // 이번 달 완료된 상담
        $completedThisMonth = Counseling::where('status', '상담 완료')
            ->whereYear('updated_at', $now->year)
            ->whereMonth('updated_at', $now->month)
            ->count();

        // 미확인 상담 (원장/관리자 확인 안 된 것)
        $unconfirmed = Counseling::where('confirmed', false)
            ->where('status', '상담 완료')
            ->count();

        return [
            Stat::make('상담 요청', $requested . '건')
                ->description('대기 중인 상담')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color($requested > 0 ? 'warning' : 'success'),
            Stat::make('상담 진행', $inProgress . '건')
                ->description('진행 중인 상담')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),
            Stat::make('이번 달 완료', $completedThisMonth . '건')
                ->description('완료된 상담')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('미확인', $unconfirmed . '건')
                ->description('원장 확인 필요')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($unconfirmed > 0 ? 'danger' : 'success'),
        ];
    }
}
