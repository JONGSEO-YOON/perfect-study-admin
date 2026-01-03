<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $totalClassrooms = Classroom::count();
        $totalTeachers = Teacher::count();

        // 이번 달 신규 학생 수
        $newStudentsThisMonth = Student::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            Stat::make('신규 학생 수', $newStudentsThisMonth)
                ->description('이번 달 새로 등록된 학생')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
            Stat::make('전체 학생 수', $totalStudents)
                ->description('현재 등록된 전체 학생')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('전체 반 수', $totalClassrooms)
                ->description('운영 중인 반')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make('전체 강사 수', $totalTeachers)
                ->description('재직 중인 강사')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
}