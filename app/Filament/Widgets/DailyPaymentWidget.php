<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyPaymentWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getHeading(): string
    {
        return '수납 현황';
    }

    protected function getStats(): array
    {
        $today = Carbon::today('Asia/Seoul');
        $academyId = self::currentAcademyId();

        // 오늘 수납 (결제 완료)
        $todayPaid = Payment::where('payment_status', 'paid')
            ->whereDate('approved_at', $today);
        if ($academyId) {
            $todayPaid->where('payments.academy_id', $academyId);
        }
        $paidCount = (clone $todayPaid)->count();
        $paidAmount = (clone $todayPaid)->sum('amount');

        // 오늘 환불 (취소)
        $todayCancelled = Payment::where('payment_status', 'cancelled')
            ->whereDate('cancelled_at', $today);
        if ($academyId) {
            $todayCancelled->where('payments.academy_id', $academyId);
        }
        $cancelledCount = (clone $todayCancelled)->count();
        $cancelledAmount = (clone $todayCancelled)->sum('amount');

        // 미납 (pending 상태)
        $unpaid = Payment::where('payment_status', 'pending');
        if ($academyId) {
            $unpaid->where('payments.academy_id', $academyId);
        }
        $unpaidCount = (clone $unpaid)->count();
        $unpaidAmount = (clone $unpaid)->sum('amount');

        return [
            Stat::make('오늘 수납', number_format($paidAmount) . '원')
                ->description($paidCount . '건')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('오늘 환불', number_format($cancelledAmount) . '원')
                ->description($cancelledCount . '건')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),
            Stat::make('미납', number_format($unpaidAmount) . '원')
                ->description($unpaidCount . '건 미납')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($unpaidCount > 0 ? 'danger' : 'success'),
        ];
    }

    private static function currentAcademyId(): ?int
    {
        if (app()->has('current_academy') && app('current_academy')) {
            return app('current_academy')->id;
        }
        return auth()->check() ? auth()->user()->academy_id : null;
    }
}
