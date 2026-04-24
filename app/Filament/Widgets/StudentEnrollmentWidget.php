<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentEnrollmentWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getHeading(): string
    {
        return '신규/퇴원생 현황';
    }

    protected function getStats(): array
    {
        $now = Carbon::now('Asia/Seoul');
        $academyId = self::currentAcademyId();

        $newQuery = Student::whereYear('initially_attended_at', $now->year)
            ->whereMonth('initially_attended_at', $now->month);

        $withdrawnQuery = Student::whereHas('user', function ($q) use ($now) {
            $q->where('is_active', false)
                ->whereYear('updated_at', $now->year)
                ->whereMonth('updated_at', $now->month);
        });

        $activeQuery = Student::whereHas('user', fn($q) => $q->where('is_active', true));

        if ($academyId) {
            $newQuery->where('students.academy_id', $academyId);
            $withdrawnQuery->where('students.academy_id', $academyId);
            $activeQuery->where('students.academy_id', $academyId);
        }

        return [
            Stat::make('이번 달 신규', $newQuery->count() . '명')
                ->description('신규 등록 학생')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
            Stat::make('이번 달 퇴원', $withdrawnQuery->count() . '명')
                ->description('퇴원 처리된 학생')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
            Stat::make('현재 재원생', $activeQuery->count() . '명')
                ->description('활성 학생 수')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
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
