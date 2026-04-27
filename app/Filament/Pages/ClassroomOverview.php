<?php

namespace App\Filament\Pages;

use App\Exports\ClassroomOverviewExport;
use Filament\Actions\Action;
use Filament\Pages\Page;
use App\Models\Classroom;
use App\Models\GradeSystem;
use Maatwebsite\Excel\Facades\Excel;

class ClassroomOverview extends Page
{
    protected static ?string $navigationIcon = null;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?string $navigationLabel = '반 전체 현황';

    protected static ?string $title = '반 전체 현황';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.classroom-overview';

    private static ?array $gradeMap = null;

    public static function canAccess(): bool
    {
        if (!\App\Models\Academy::isMenuGroupVisibleForCurrentUser('classroom')) {
            return false;
        }
        return auth()->user()->isRoleAbove('admin', true)
            || !auth()->user()->userable instanceof \App\Models\Teacher;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExcel')
                ->label('엑셀 다운로드')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $filename = '반전체현황_' . now()->format('Ymd_His') . '.xlsx';
                    return Excel::download(new ClassroomOverviewExport(), $filename);
                }),
        ];
    }

    private static function getGradeMap(): array
    {
        if (self::$gradeMap === null) {
            self::$gradeMap = GradeSystem::orderBy('sequential_order')
                ->pluck('display_name', 'id')
                ->toArray();
        }

        return self::$gradeMap;
    }

    public function getViewData(): array
    {
        $dayLabels = [
            'mon' => '월',
            'tue' => '화',
            'wed' => '수',
            'thu' => '목',
            'fri' => '금',
            'sat' => '토',
            'sun' => '일',
        ];

        $classrooms = Classroom::with(['teacher.user', 'subTeacher.user', 'students.user'])
            ->orderBy('name')
            ->get()
            ->map(function ($classroom) use ($dayLabels) {
                $gradeMap = self::getGradeMap();
                $grades = collect($classroom->target_grades ?? [])
                    ->map(fn($id) => $gradeMap[$id] ?? $id)
                    ->join(', ');

                $timetableLines = [];
                if ($classroom->timetable) {
                    $dayOrder = array_keys($dayLabels);
                    $sorted = collect($classroom->timetable)
                        ->sortBy(fn($v, $k) => array_search($k, $dayOrder));
                    foreach ($sorted as $day => $times) {
                        $dayLabel = $dayLabels[$day] ?? $day;
                        $start = $times['start'] ?? '';
                        $end = $times['end'] ?? '';
                        $timetableLines[] = "{$dayLabel} {$start}~{$end}";
                    }
                }

                $students = $classroom->students
                    ->filter(fn($s) => $s->status === 'enrolled')
                    ->sortBy(fn($s) => $s->user->name ?? '')
                    ->values();

                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'grades' => $grades,
                    'level' => $classroom->target_level,
                    'started_at' => $classroom->started_at,
                    'ended_at' => $classroom->ended_at,
                    'timetable' => $timetableLines,
                    'teacher' => $classroom->teacher?->user?->name ?? '-',
                    'sub_teacher' => $classroom->subTeacher?->user?->name ?? '-',
                    'remark' => $classroom->remark,
                    'students' => $students,
                    'students_count' => $students->count(),
                ];
            });

        return [
            'classrooms' => $classrooms,
        ];
    }
}
