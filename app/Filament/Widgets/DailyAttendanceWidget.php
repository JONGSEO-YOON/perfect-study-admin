<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceLog;
use App\Models\Classroom;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyAttendanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getHeading(): string
    {
        return '출결 현황';
    }

    protected function getStats(): array
    {
        $today = Carbon::today('Asia/Seoul');
        $currentDay = strtolower($today->format('D'));

        // 오늘 수업이 있는 학생 수 (정규)
        $classroomsWithClassToday = Classroom::whereNull('ended_at')
            ->orWhere('ended_at', '>=', $today)
            ->get()
            ->filter(fn($c) => isset($c->timetable[$currentDay]));

        $totalStudentsToday = 0;
        foreach ($classroomsWithClassToday as $classroom) {
            $totalStudentsToday += $classroom->students()
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->count();
        }

        // 오늘 출석 기록
        $todayLogs = AttendanceLog::whereDate('created_at', $today);
        $attendedCount = (clone $todayLogs)->whereNotNull('check_in_time')->count();
        $lateCount = (clone $todayLogs)->where('is_late', true)->count();
        $absentCount = max(0, $totalStudentsToday - $attendedCount);

        return [
            Stat::make('오늘 대상 인원', $totalStudentsToday . '명')
                ->description('정규 수업 대상 학생')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('출석', $attendedCount . '명')
                ->description('등원 완료')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('지각', $lateCount . '명')
                ->description('수업 시작 후 등원')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('미등원', $absentCount . '명')
                ->description('아직 등원하지 않은 학생')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
