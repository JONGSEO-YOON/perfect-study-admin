<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use App\Models\GradeSystem;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewClassroom extends ViewRecord
{
    protected static string $resource = ClassroomResource::class;

    protected static ?string $title = '반 상세현황';

    public function getBreadcrumb(): string
    {
        return '반 상세현황';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('수정')
                ->modalHeading('반 수정하기')
                ->modalWidth('2xl'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('반 정보')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('반 이름'),
                                TextEntry::make('teacher.user.name')
                                    ->label('담임'),
                                TextEntry::make('subTeacher.user.name')
                                    ->label('부담임')
                                    ->default('-'),
                                TextEntry::make('target_level')
                                    ->label('레벨'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('target_grades')
                                    ->label('학년')
                                    ->state(function ($record) {
                                        return GradeSystem::query()
                                            ->whereIn('id', $record->target_grades ?? [])
                                            ->orderBy('sequential_order')
                                            ->pluck('display_name')
                                            ->join(', ') ?: '-';
                                    }),
                                TextEntry::make('started_at')
                                    ->label('개설일')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('Y-m-d') : '-'),
                                TextEntry::make('ended_at')
                                    ->label('종료일')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('Y-m-d') : '-'),
                                TextEntry::make('students_count')
                                    ->label('학생 수')
                                    ->state(fn($record) => $record->students()->count() . '명'),
                            ]),
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('timetable')
                                    ->label('시간표')
                                    ->state(function ($record) {
                                        $days = ['mon' => '월', 'tue' => '화', 'wed' => '수', 'thu' => '목', 'fri' => '금', 'sat' => '토', 'sun' => '일'];
                                        $timetable = $record->timetable ?? [];
                                        $lines = [];
                                        foreach ($days as $key => $label) {
                                            if (!empty($timetable[$key]['active'])) {
                                                $start = $timetable[$key]['start'] ?? '';
                                                $end = $timetable[$key]['end'] ?? '';
                                                $lines[] = "{$label} {$start} ~ {$end}";
                                            }
                                        }
                                        return empty($lines) ? '-' : implode(' / ', $lines);
                                    }),
                            ]),
                        TextEntry::make('remark')
                            ->label('비고')
                            ->default('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('학생 명단')
                    ->schema([
                        ViewEntry::make('students_roster')
                            ->view('filament.components.classroom-roster')
                            ->viewData([
                                'record' => $this->record,
                            ]),
                    ]),
            ]);
    }

}
