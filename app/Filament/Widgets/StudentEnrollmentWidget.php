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

        // 이번 달 신규 학생 (initially_attended_at 기준)
        $newStudentsThisMonth = Student::whereYear('initially_attended_at', $now->year)
            ->whereMonth('initially_attended_at', $now->month)
            ->count();

        // 이번 달 퇴원생 (is_active가 false로 변경된 학생, updated_at 기준)
        $withdrawnThisMonth = Student::whereHas('user', function ($q) use ($now) {
            $q->where('is_active', false)
                ->whereYear('updated_at', $now->year)
                ->whereMonth('updated_at', $now->month);
        })->count();

        // 현재 재원생 (활성 학생)
        $activeStudents = Student::whereHas('user', fn($q) => $q->where('is_active', true))->count();

        return [
            Stat::make('이번 달 신규', $newStudentsThisMonth . '명')
                ->description('신규 등록 학생')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
            Stat::make('이번 달 퇴원', $withdrawnThisMonth . '명')
                ->description('퇴원 처리된 학생')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
            Stat::make('현재 재원생', $activeStudents . '명')
                ->description('활성 학생 수')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
