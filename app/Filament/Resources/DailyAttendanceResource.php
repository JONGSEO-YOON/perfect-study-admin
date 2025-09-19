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
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyAttendanceResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?string $navigationLabel = '일별출결 현황';

    protected static ?int $navigationSort = 7;

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

                TextColumn::make('attendance_status')
                    ->label('구분')
                    ->width('80px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $attendanceLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->latest()
                            ->first();

                        if (!$attendanceLog) {
                            return '';
                        }

                        if ($attendanceLog->is_absent) {
                            return '결석';
                        }

                        if ($attendanceLog->check_in_time || $attendanceLog->check_out_time) {
                            return '등원';
                        }

                        return '';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '등원' => 'success',
                        '결석' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('supplementary_status')
                    ->label('보충 여부')
                    ->badge()
                    ->width('100px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        // 해당 날짜의 출석 기록 조회
                        $attendanceRecord = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->latest()
                            ->first();

                        if (!$attendanceRecord) {
                            return '';
                        }

                        // 결석이 아니면 표시
                        if ($attendanceRecord->is_absent) {
                            return '';
                        }

                        return $attendanceRecord->is_supplementary ? 'supplementary' : 'regular';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'supplementary' => 'warning',
                        'regular' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'supplementary' => '보충',
                        'regular' => '정규',
                        default => '',
                    }),
                TextColumn::make('check_in_time')
                    ->label('등원 시간')
                    ->width('100px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $attendanceLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->first();

                        if ($attendanceLog && $attendanceLog->check_in_time) {
                            return \Carbon\Carbon::parse($attendanceLog->check_in_time)->format('H:i');
                        }
                        return '';
                    }),
                TextColumn::make('check_out_time')
                    ->label('하원 시간')
                    ->width('100px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $attendanceLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->first();

                        if ($attendanceLog && $attendanceLog->check_out_time) {
                            return \Carbon\Carbon::parse($attendanceLog->check_out_time)->format('H:i');
                        }
                        return '';
                    }),
                TextColumn::make('late_status')
                    ->label('지각')
                    ->width('60px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        // 해당 날짜의 지각 기록이 있는지 확인
                        $hasLateRecord = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->where('is_late', true)
                            ->exists();

                        return $hasLateRecord ? '지각' : '';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '지각' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('memo')
                    ->label('메모')
                    ->width('150px')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $attendanceLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->latest()
                            ->first();

                        if ($attendanceLog && $attendanceLog->memo) {
                            return $attendanceLog->memo;
                        }
                        return '';
                    })
                    ->limit(50),

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
                        $query = Classroom::query()->orderBy('name');

                        if (!auth()->user()->isRoleAbove('manager', true)) {
                            $query->where('teacher_id', auth()->user()->userable->id);
                        }

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
                    ->label('오늘 기록 추가')
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
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                if ($state) {
                                    $filters = $livewire->getTableFiltersForm()->getState();
                                    $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                                    $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                                    $existingLog = \App\Models\AttendanceLog::where('student_id', $state)
                                        ->whereDate('created_at', $selectedDate)
                                        ->first();

                                    if ($existingLog) {
                                        $set('existing_log_id', $existingLog->id);
                                        $set('is_late', $existingLog->is_late ?? false);
                                        $set('is_absent', $existingLog->is_absent ?? false);
                                        $set('is_supplementary', $existingLog->is_supplementary ?? false);
                                        $set('memo', $existingLog->memo);
                                        $set('check_in_time_only', $existingLog->check_in_time ? $existingLog->check_in_time->format('H:i') : null);
                                        $set('check_out_time_only', $existingLog->check_out_time ? $existingLog->check_out_time->format('H:i') : null);
                                    } else {
                                        $set('existing_log_id', null);
                                        $set('is_late', false);
                                        $set('is_absent', false);
                                        $set('is_supplementary', false);
                                        $set('memo', null);
                                        $set('check_in_time_only', null);
                                        $set('check_out_time_only', null);
                                    }
                                }
                            }),
                        TimePicker::make('check_in_time_only')
                            ->label('등원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        TimePicker::make('check_out_time_only')
                            ->label('하원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        Toggle::make('is_late')
                            ->label('지각 여부')
                            ->default(false)
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_supplementary')
                            ->label('보충 수업 여부')
                            ->default(false)
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_absent')
                            ->label('결석 여부')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('is_late', false);
                                    $set('is_supplementary', false);
                                }
                            }),
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3),

                        \Filament\Forms\Components\Hidden::make('existing_log_id')
                    ])
                    ->action(function ($data, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $today = \Carbon\Carbon::parse($selectedDate);

                        $attendanceData = [
                            'student_id' => $data['student_id'],
                            'is_late' => $data['is_late'] ?? false,
                            'is_absent' => $data['is_absent'] ?? false,
                            'is_supplementary' => $data['is_supplementary'] ?? false,
                            'memo' => $data['memo'],
                            'check_in_time' => $data['check_in_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_in_time_only']) : null,
                            'check_out_time' => $data['check_out_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_out_time_only']) : null,
                        ];

                        if ($data['existing_log_id']) {
                            // 기존 기록 업데이트
                            $existingLog = AttendanceLog::find($data['existing_log_id']);
                            if ($existingLog) {
                                $existingLog->update($attendanceData);
                            }
                        } else {
                            // 새 기록 생성
                            $attendanceLog = new AttendanceLog($attendanceData);
                            $attendanceLog->timestamps = false;

                            // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 선택한 날짜로 설정
                            if ($attendanceLog->check_in_time) {
                                $attendanceLog->created_at = $attendanceLog->check_in_time;
                            } elseif ($attendanceLog->check_out_time) {
                                $attendanceLog->created_at = $attendanceLog->check_out_time;
                            } else {
                                $attendanceLog->created_at = $today;
                            }

                            $attendanceLog->updated_at = now();
                            $attendanceLog->save();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('create_attendance_for_student')
                    ->label('생성')
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->modalHeading(fn($record) => $record->user->name . ' 학생 출석 기록 생성')
                    ->form([
                        TimePicker::make('check_in_time_only')
                            ->label('등원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        TimePicker::make('check_out_time_only')
                            ->label('하원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        Toggle::make('is_late')
                            ->label('지각 여부')
                            ->default(false)
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_supplementary')
                            ->label('보충 수업 여부')
                            ->default(false)
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_absent')
                            ->label('결석 여부')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('is_late', false);
                                    $set('is_supplementary', false);
                                }
                            }),
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3),
                    ])
                    ->action(function ($data, $record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $today = \Carbon\Carbon::parse($selectedDate);

                        $attendanceData = [
                            'student_id' => $record->id,
                            'is_late' => $data['is_late'] ?? false,
                            'is_absent' => $data['is_absent'] ?? false,
                            'is_supplementary' => $data['is_supplementary'] ?? false,
                            'memo' => $data['memo'],
                            'check_in_time' => $data['check_in_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_in_time_only']) : null,
                            'check_out_time' => $data['check_out_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_out_time_only']) : null,
                        ];

                        $attendanceLog = new AttendanceLog($attendanceData);
                        $attendanceLog->timestamps = false;

                        // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 선택한 날짜로 설정
                        if ($attendanceLog->check_in_time) {
                            $attendanceLog->created_at = $attendanceLog->check_in_time;
                        } elseif ($attendanceLog->check_out_time) {
                            $attendanceLog->created_at = $attendanceLog->check_out_time;
                        } else {
                            $attendanceLog->created_at = $today;
                        }

                        $attendanceLog->updated_at = now();
                        $attendanceLog->save();
                    })
                    ->visible(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        return !$record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->exists();
                    }),
                Tables\Actions\Action::make('edit_attendance')
                    ->label('수정')
                    ->icon('heroicon-m-pencil')
                    ->color('primary')
                    ->form([
                        TimePicker::make('check_in_time_only')
                            ->label('등원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        TimePicker::make('check_out_time_only')
                            ->label('하원 시간')
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        Toggle::make('is_late')
                            ->label('지각 여부')
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_supplementary')
                            ->label('보충 수업 여부')
                            ->visible(fn($get) => !$get('is_absent')),
                        Toggle::make('is_absent')
                            ->label('결석 여부')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('is_late', false);
                                    $set('is_supplementary', false);
                                }
                            }),
                        Textarea::make('memo')
                            ->label('메모')
                            ->rows(3)

                    ])
                    ->fillForm(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $firstLog = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->first();

                        if ($firstLog) {
                            return [
                                'is_late' => $firstLog->is_late ?? false,
                                'is_absent' => $firstLog->is_absent ?? false,
                                'is_supplementary' => $firstLog->is_supplementary ?? false,
                                'memo' => $firstLog->memo,
                                'check_in_time_only' => $firstLog->check_in_time ? $firstLog->check_in_time->format('H:i') : null,
                                'check_out_time_only' => $firstLog->check_out_time ? $firstLog->check_out_time->format('H:i') : null,
                            ];
                        }

                        return [];
                    })
                    ->action(function ($data, $record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');
                        $today = \Carbon\Carbon::parse($selectedDate);

                        // 해당 날짜의 출석 기록 찾기
                        $log = $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->first();

                        if ($log) {
                            $updateData = [
                                'is_late' => $data['is_late'] ?? false,
                                'is_absent' => $data['is_absent'] ?? false,
                                'is_supplementary' => $data['is_supplementary'] ?? false,
                                'memo' => $data['memo'],
                                'check_in_time' => $data['check_in_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_in_time_only']) : null,
                                'check_out_time' => $data['check_out_time_only'] ? $today->copy()->setTimeFromTimeString($data['check_out_time_only']) : null,
                            ];

                            $log->update($updateData);
                        }
                    })
                    ->visible(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        return $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->exists();
                    }),
                Tables\Actions\Action::make('delete_attendance')
                    ->label('삭제')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('출결 기록 삭제')
                    ->modalDescription(fn($record) => $record->user->name . '의 오늘 출결 기록을 삭제하시겠습니까?')
                    ->action(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->delete();
                    })
                    ->visible(function ($record, $livewire) {
                        $filters = $livewire->getTableFiltersForm()->getState();
                        $selectedDate = $filters['date']['date'] ?? now()->format('Y-m-d');
                        $selectedDate = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');

                        return $record->attendanceLogs()
                            ->whereDate('created_at', $selectedDate)
                            ->exists();
                    }),
                Tables\Actions\Action::make('attendance_detail')
                    ->label('출석')
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
