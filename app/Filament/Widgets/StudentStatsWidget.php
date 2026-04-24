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
        $academyId = self::currentAcademyId();

        $studentsQuery = Student::query();
        $classroomsQuery = Classroom::query();
        $teachersQuery = Teacher::query();
        $newStudentsQuery = Student::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        if ($academyId) {
            $studentsQuery->where('students.academy_id', $academyId);
            $classroomsQuery->where('classrooms.academy_id', $academyId);
            $teachersQuery->where('teachers.academy_id', $academyId);
            $newStudentsQuery->where('students.academy_id', $academyId);
        }

        return [
            Stat::make('신규 학생 수', $newStudentsQuery->count())
                ->description('이번 달 새로 등록된 학생')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
            Stat::make('전체 학생 수', $studentsQuery->count())
                ->description('현재 등록된 전체 학생')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('전체 반 수', $classroomsQuery->count())
                ->description('운영 중인 반')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make('전체 강사 수', $teachersQuery->count())
                ->description('재직 중인 강사')
                ->descriptionIcon('heroicon-m-user-group')
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
