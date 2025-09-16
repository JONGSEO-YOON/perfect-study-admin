<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyAttendanceResource\Pages;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Models\Classroom;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyAttendanceResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = '출결 관리';

    protected static ?string $navigationLabel = '일별출결 현황';

    protected static ?int $navigationSort = 1;

    public static function getBreadcrumb(): string
    {
        return '일별출결 현황';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query = $query->with(['classrooms', 'user', 'school', 'gradeSystem', 'latestCheckIn', 'latestCheckOut']);

                if (!auth()->user()->isRoleAbove('manager', true)) {
                    $query->whereHas('classrooms', function ($q) {
                        $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                    });
                }

                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex()
                    ->width('60px'),

                TextColumn::make('user.name')
                    ->label('학생명')
                    ->searchable()
                    ->sortable()
                    ->width('120px'),
                TextColumn::make('gradeSystem.display_name')
                    ->label('학년')
                    ->sortable()
                    ->width('80px'),
                TextColumn::make('classrooms.name')
                    ->label('수업 반')
                    ->badge()
                    ->separator(',')
                    ->width('120px'),
                TextColumn::make('late_status')
                    ->label('지각')
                    ->width('60px')
                    ->state(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        // 디버깅: 해당 날짜의 모든 출결 기록 확인
                        $allLogs = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->get();

                        $lateCount = $allLogs->where('is_late', 1)->count();

                        if ($lateCount > 0) {
                            return '지각';
                        }

                        // 디버깅 정보 표시
                        return $allLogs->count() > 0 ? "기록{$allLogs->count()}" : '-';
                    }),
                TextColumn::make('supplementary_status')
                    ->label('보충 여부')
                    ->badge()
                    ->width('100px')
                    ->state(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        // 해당 날짜의 출석 기록 조회
                        $attendanceRecord = $record->attendanceLogsForDate($selectedDate)
                            ->latest()
                            ->first();

                        if (!$attendanceRecord) {
                            return 'none'; // 출석 기록 없음
                        }

                        return $attendanceRecord->is_supplementary ? 'supplementary' : 'regular';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'supplementary' => 'warning',
                        'regular' => 'success',
                        'none' => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'supplementary' => '보충',
                        'regular' => '정규',
                        'none' => '-',
                    }),
                TextColumn::make('latestCheckIn.created_at')
                    ->label('등원 시간')
                    ->width('100px')
                    ->formatStateUsing(function ($state) {
                        return $state ? $state->format('H:i') : '-';
                    }),
                TextColumn::make('latestCheckOut.created_at')
                    ->label('하원 시간')
                    ->width('100px')
                    ->formatStateUsing(function ($state) {
                        return $state ? $state->format('H:i') : '-';
                    }),

                // TextColumn::make('school.name')
                //     ->label('학교')
                //     ->searchable()
                //     ->sortable(),


            ])
            ->defaultPaginationPageOption(25)
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->label('날짜')
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->format('Y-m-d')
                            ->native(false)
                    ])
                    ->default([
                        'date' => now()->format('Y-m-d')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedDate = $data['date'] ?? now()->format('Y-m-d');
                        // 날짜만 추출 (시간 제거)
                        $selectedDate = Carbon::parse($selectedDate)->format('Y-m-d');
                        $selectedDayOfWeek = Carbon::parse($selectedDate)->format('w');
                        $dayEnglish = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][$selectedDayOfWeek];

                        $studentsWithSupplementaryAttendance = \App\Models\AttendanceLog::where('is_supplementary', true)
                            ->whereDate('created_at', $selectedDate)
                            ->pluck('student_id')
                            ->unique();
                        return $query->where(function ($query) use ($dayEnglish, $studentsWithSupplementaryAttendance) {
                            // 정규 수업이 있는 학생들
                            $query->whereHas('classrooms', function (Builder $classroomQuery) use ($dayEnglish) {
                                $classroomQuery->whereNotNull('timetable->' . $dayEnglish);
                            })
                                // 또는 보충 출석 기록이 있는 학생들
                                ->orWhereIn('id', $studentsWithSupplementaryAttendance);
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }

                        $date = Carbon::parse($data['date']);
                        return '날짜: ' . $date->format('Y년 n월 j일') . ' (' . $date->locale('ko')->dayName . ')';
                    }),
                SelectFilter::make('classroom_id')
                    ->label('반')
                    ->options(function () {
                        $currentDay = strtolower(now()->format('D')); // mon, tue, wed, thu, fri, sat, sun
                        $query = Classroom::query()->orderBy('name');

                        if (!auth()->user()->isRoleAbove('manager', true)) {
                            $query->where('teacher_id', auth()->user()->userable->id);
                        }

                        // 오늘 수업이 있는 반만 필터링
                        $query->whereNotNull('timetable->' . $currentDay);

                        return $query->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, $data) {
                        $classroomId = $data["value"] ?? null;
                        $query->when($classroomId, function ($query, $classroomId) {
                            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                                $q->where('classrooms.id', $classroomId);
                            });
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->defaultSort('id')
            ->headerActions([
                Tables\Actions\Action::make('create_attendance')
                    ->label('출석 기록 추가')
                    ->icon('heroicon-m-plus')
                    ->form([
                        Select::make('student_id')
                            ->label('학생')
                            ->options(function () {
                                $query = Student::with('user');

                                if (!auth()->user()->isRoleAbove('manager', true)) {
                                    $query->whereHas('classrooms', function ($q) {
                                        $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                                    });
                                }

                                return $query->get()->pluck('user.name', 'id');
                            })
                            ->searchable()
                            ->required(),
                        ToggleButtons::make('type')
                            ->label('타입')
                            ->inline()
                            ->options([
                                'in' => '등원',
                                'out' => '하원'
                            ])
                            ->colors([
                                'in' => 'success',
                                'out' => 'danger'
                            ])
                            ->default('in')
                            ->required(),
                        Select::make('classroom_id')
                            ->label('수업 반')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                if (!$studentId) {
                                    return [];
                                }
                                $student = Student::find($studentId);
                                if (!$student) {
                                    return [];
                                }

                                // 오늘 수업이 있는 반만 필터링
                                $currentDay = strtolower(now()->format('D'));
                                return $student->classrooms->filter(function ($classroom) use ($currentDay) {
                                    return isset($classroom->timetable[$currentDay]);
                                })->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder('보충 수업인 경우 선택하지 마세요')
                            ->reactive(),
                        Toggle::make('is_late')
                            ->label('지각 여부')
                            ->default(false),
                        Toggle::make('is_supplementary')
                            ->label('보충 수업 여부')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('classroom_id', null);
                                }
                            }),
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3),
                        DateTimePicker::make('attendance_time')
                            ->label(fn(callable $get) => $get('type') === 'in' ? '등원 시간' : '하원 시간')
                            ->default(now())
                            ->required()
                            ->displayFormat('Y-m-d H:i')
                            ->native(false)
                            ->dehydrated(false)
                    ])
                    ->action(function ($data) {
                        AttendanceLog::create([
                            'student_id' => $data['student_id'],
                            'classroom_id' => $data['is_supplementary'] ? null : $data['classroom_id'],
                            'type' => $data['type'] ?? 'in',
                            'is_late' => $data['is_late'],
                            'is_supplementary' => $data['is_supplementary'],
                            'memo' => $data['memo'],
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('attendance_detail')
                    ->label('출석 상세')
                    ->icon('heroicon-m-calendar')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.components.modals.attendance', [
                        'shareLink' =>  route("attendance-calendar", [
                            "studentData" => base64_encode(json_encode([
                                'student_id' => $record->id,
                                'name' => $record->user->name
                            ]))
                        ]),
                    ]))
                    ->modalWidth('7xl'),
                Tables\Actions\Action::make('edit_attendance')
                    ->label('출결 수정')
                    ->icon('heroicon-m-pencil')
                    ->color('primary')
                    ->form([
                        Select::make('attendance_log_id')
                            ->label('출결 기록')
                            ->options(function ($record) {
                                $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                                $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                                return $record->attendanceLogs()
                                    ->whereDate('created_at', $selectedDate)
                                    ->get()
                                    ->mapWithKeys(function ($log) {
                                        return [$log->id => $log->type . ' - ' . $log->created_at->format('H:i')];
                                    });
                            })
                            ->required(),
                        ToggleButtons::make('type')
                            ->label('타입')
                            ->inline()
                            ->options([
                                'in' => '등원',
                                'out' => '하원'
                            ])
                            ->colors([
                                'in' => 'success',
                                'out' => 'danger'
                            ])
                            ->required(),
                        Toggle::make('is_late')
                            ->label('지각 여부'),
                        Toggle::make('is_supplementary')
                            ->label('보충 수업 여부'),
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3),
                        DateTimePicker::make('created_at')
                            ->label('시간')
                            ->required()
                            ->displayFormat('Y-m-d H:i')
                            ->native(false)
                    ])
                    ->fillForm(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $firstLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->first();

                        if ($firstLog) {
                            return [
                                'attendance_log_id' => $firstLog->id,
                                'type' => $firstLog->type,
                                'is_late' => $firstLog->is_late,
                                'is_supplementary' => $firstLog->is_supplementary,
                                'memo' => $firstLog->memo,
                                'created_at' => $firstLog->created_at,
                            ];
                        }

                        return [];
                    })
                    ->action(function ($data, $record) {
                        if (isset($data['attendance_log_id'])) {
                            $log = AttendanceLog::find($data['attendance_log_id']);
                            if ($log) {
                                $log->update([
                                    'type' => $data['type'],
                                    'is_late' => $data['is_late'] ?? false,
                                    'is_supplementary' => $data['is_supplementary'] ?? false,
                                    'memo' => $data['memo'],
                                    'created_at' => $data['created_at'],
                                ]);
                            }
                        }
                    })
                    ->visible(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        return $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->exists();
                    }),
                Tables\Actions\Action::make('delete_attendance')
                    ->label('출결 삭제')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('출결 기록 삭제')
                    ->modalDescription(fn($record) => $record->user->name . '의 오늘 출결 기록을 삭제하시겠습니까?')
                    ->action(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->delete();
                    })
                    ->visible(function ($record) {
                        $selectedDate = request()->input('tableFilters.date.date', now()->format('Y-m-d'));
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        return $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->exists();
                    }),
            ])
            ->emptyStateHeading('선택된 날짜에 수업이 있는 학생이 없습니다.')
            ->emptyStateDescription('선택된 날짜에 수업이 예정된 학생이 없습니다.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyAttendance::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
