<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestSheetResource\Pages;
use App\Filament\Resources\TestSheetResource\RelationManagers;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Student;
use App\Models\TestSheet;
use App\Models\TempData;
use App\Models\User;
use App\Models\WrongAnswerTestSheet;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Academy;

class TestSheetResource extends Resource
{
    protected static ?string $model = TestSheet::class;

    protected static ?string $navigationLabel = '문제지 관리';

    protected static ?string $title = '문제지 관리';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = '문제 관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function canViewAny(): bool
    {
        if (!\App\Models\Academy::isMenuGroupVisibleForCurrentUser('tests')) {
            return false;
        }
        return auth()->user()->userable instanceof \App\Models\Teacher;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $role = auth()->user()->role;

                return $query->withSharedExams()
                    ->originals()
                    ->whereNotNull('target_group')
                    ->when(in_array($role, ['general', 'manager']), function ($query) {
                        // general/manager: 자기가 올린 문제지 + 강사 할당된 문제지만
                        $teacher = auth()->user()->userable;
                        return $query->where(function ($subQuery) use ($teacher) {
                            $subQuery->where('user_id', auth()->user()->id)
                                ->orWhereHas('teachers', function ($q) use ($teacher) {
                                    $q->where('teachers.id', $teacher->id);
                                });
                        });
                    });
            })
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('출제자')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('시험지 명')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tags')
                    ->label('태그')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('question_category')
                    ->view('filament.components.columns.testsheet-question-category-render')
                    ->label('문제 유형'),
                // TextColumn::make('name')
                //     ->label('시험지 명')
                //     ->sortable()
                //     ->searchable()
                //     ->html()
                //     ->formatStateUsing(function ($record) {
                //         $question_count = count($record->questions);
                //         return <<<EOF
                //             $record->name <br />
                //             <span class="text-primary-500 mt-1 font-medium">{$question_count}문항 |</span>
                //             <span class="text-primary-500 mt-1 font-semibold">{$record->scopes[0]}</span>

                //         EOF;
                //     }),

                TextColumn::make('target_group_label')
                    ->state(true)
                    ->label('출제 대상')
                    // ->searchable()
                    ->html()
                    ->formatStateUsing(function ($record) {
                        if ($record->target_group === 'grade') {
                            return self::formatGradeTarget($record->target_grades);
                        } else if ($record->target_group === 'level') {
                            return self::formatLevelTarget($record->target_grades, $record->target_levels);
                        } else if ($record->target_group === 'classroom') {
                            return self::formatClassroomTarget($record->target_classrooms);
                        } else if ($record->target_group === 'student') {
                            return self::formatStudentTarget($record->target_students);
                        }
                        return '';
                    }),
                TextColumn::make('share_scope')
                    ->label('공유')
                    ->formatStateUsing(fn($record) => match ($record->share_scope) {
                        'all' => '전체 공개',
                        'restricted' => '일부 공개',
                        default => '',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'all' => 'success',
                        'restricted' => 'warning',
                        default => 'gray',
                    })
                    ->visible(fn() => auth()->user()->role === 'root_admin' || auth()->user()->role === 'admin'),
                TextColumn::make('academy.name')
                    ->label('학원')
                    ->visible(fn() => auth()->user()->role === 'root_admin'),
                TextColumn::make('start_date')
                    ->date('Y-m-d H:i')
                    ->label('출제일')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date('Y-m-d H:i')
                    ->label('마감일')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('출제 상태')
                    ->formatStateUsing(function ($record) {
                        // 2차 오답 테스트 확인
                        $secondRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                            ->where('retry_count', 2)
                            ->with('testSheet')  // N+1 방지를 위해 with 사용
                            ->first();

                        $auto = ($record->is_auto) ? '(자동 출제)' : '';

                        if ($secondRetryTest) {
                            $status = $secondRetryTest->testSheet->status;

                            if ($status === 'pending') {
                                return '출제 대기 (오답테스트)';
                            } else if ($status === 'progress') {
                                return '출제 중 (오답테스트)';
                            } else if ($status === 'completed') {
                                return '출제 종료 (오답테스트)';
                            }
                        } else {
                            if ($record->status === 'pending') {
                                return '출제 대기 ' . $auto;
                            } else if ($record->status === 'progress') {
                                return '출제 중 ' . $auto;
                            } else if ($record->status === 'completed') {
                                return '출제 종료 ' . $auto;
                            }
                        }
                    })
                    ->sortable()
                    ->badge()
                    ->color(function (string $state, $record) {
                        // 2차 오답 테스트 확인
                        $secondRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                            ->where('retry_count', 2)
                            ->with('testSheet')
                            ->first();

                        // 실제 상태 결정 (2차 오답 테스트가 있으면 그것의 상태, 없으면 record의 상태)
                        $actualStatus = $secondRetryTest
                            ? $secondRetryTest->testSheet->status
                            : $record->status;

                        return match ($actualStatus) {
                            'pending' => 'gray',
                            'draft' => 'gray',
                            'progress' => 'primary',
                            'completed' => 'success',
                            default => 'gray',
                        };
                    })
            ])
            ->filters([
                Filter::make('duration')
                    ->columnSpan(2)
                    ->form([
                        Fieldset::make('duration')
                            ->label('조회 기간')
                            ->extraAttributes(['class' => 'border-none', 'style' => 'padding: 0; padding-top: 0.5rem;'])
                            ->schema([
                                DatePicker::make('from')
                                    ->default(now()->subDays(7)->format('Y-m-d'))
                                    ->label(false),
                                DatePicker::make('until')
                                    ->default(now()->format('Y-m-d'))
                                    ->label(false)
                            ]),
                    ])
                    ->query(function (Builder $query, $data) {
                        $from = Carbon::parse($data['from'])->startOfDay();
                        $until = Carbon::parse($data['until'])->endOfDay();
                        $query->where(function ($q) use ($from, $until) {
                            $q->where(function ($subQuery) use ($from, $until) {
                                $subQuery->whereNotNull('start_date')
                                    ->whereBetween('start_date', [$from, $until]);
                            })
                                ->orWhere(function ($subQuery) use ($from, $until) {
                                    $subQuery->whereNull('start_date')
                                        ->whereBetween('created_at', [$from, $until]);
                                });
                        });
                    }),
                Filter::make('status')
                    ->columnSpan(2)
                    ->form([
                        Fieldset::make('status')
                            ->label('출제 상태')
                            ->extraAttributes(['class' => 'border-none', 'style' => 'padding: 0; padding-top: 0.5rem;'])
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'all' => '전체',
                                        'pending' => '출제 대기',
                                        'progress' => '출제 중',
                                        'completed' => '출제 종료',
                                    ])
                                    ->native(false)
                                    ->default('all')
                                    ->label(false),
                            ]),
                    ])
                    ->query(function (Builder $query, $data) {
                        if ($data['status'] !== 'all') {
                            $query->where('status', $data['status']);
                        }
                    }),
            ], FiltersLayout::AboveContent)
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('start-test-sheet')
                    ->label('문제지 출제')
                    ->visible(fn($record) => $record->academy_id === auth()->user()->academy_id && $record->status === 'pending' && empty($record->start_date))
                    ->icon('heroicon-m-check')
                    ->modalHeading('문제지 출제')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update([
                        'status' => 'progress',
                        'start_date' => now(),
                    ])),
                Tables\Actions\Action::make('start-second-test-sheet')
                    ->label('오답 테스트 출제')
                    ->visible(function ($record) {
                        // 타 학원 문제지는 조작 불가
                        if ($record->academy_id !== auth()->user()->academy_id) {
                            return false;
                        }
                        // 원본 테스트이고 마감 상태인지 확인
                        if (!$record->isOriginal() || $record->status !== 'completed') {
                            return false;
                        }

                        //  2차 오답 테스트 존재 여부와 상태 확인
                        return WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                            ->where('retry_count', 2)
                            ->whereHas('testSheet', function ($query) {
                                $query->where('status', 'pending');
                            })
                            ->exists();
                    })
                    ->icon('heroicon-m-check')
                    ->modalHeading('문제지 출제')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // 해당하는 모든 2차 오답 테스트 업데이트
                        TestSheet::query()
                            ->whereHas('wrongAnswerTestSheets', function ($query) use ($record) {
                                $query->where('original_test_sheet_id', $record->id)
                                    ->where('retry_count', 2);
                            })
                            ->update([
                                'status' => 'progress',
                                'start_date' => now(),
                            ]);
                    }),

                Tables\Actions\Action::make('end-test-sheet')
                    ->label('문제지 마감')
                    ->color('danger')
                    ->visible(fn($record) => $record->academy_id === auth()->user()->academy_id && $record->status === 'progress')
                    ->icon('heroicon-m-check')
                    ->modalHeading('문제지 마감')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->complete()),
                Tables\Actions\Action::make('submission-status')
                    ->label('제출 현황')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('info')
                    ->visible(fn($record) => in_array($record->status, ['progress', 'completed']))
                    ->modalHeading(fn($record) => $record->name . ' - 제출 현황')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('닫기')
                    ->modalWidth('5xl')
                    ->modalContent(function ($record) {
                        // 출제 대상 학생들 조회
                        $targetStudents = $record->getTargetStudents();
                        $totalCount = $targetStudents->count();

                        // 본문제 제출한 답안들
                        $answers = \App\Models\TestSheetAnswer::where('test_sheet_id', $record->id)
                            ->where('status', 'completed')
                            ->with('user')
                            ->get()
                            ->keyBy('user_id');

                        // 오답유사유형 (wrong-answer retry) 매핑
                        // user_id별 첫 번째 오답 시트 (retry_count=1) 정보
                        $wrongAnswerSheets = \App\Models\WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                            ->where('retry_count', 1)
                            ->with('testSheet')
                            ->get()
                            ->keyBy('user_id');

                        $wrongAnswerSheetIds = $wrongAnswerSheets->pluck('test_sheet_id')->filter()->all();
                        $wrongAnswerAnswers = empty($wrongAnswerSheetIds)
                            ? collect()
                            : \App\Models\TestSheetAnswer::whereIn('test_sheet_id', $wrongAnswerSheetIds)
                                ->where('status', 'completed')
                                ->get()
                                ->groupBy(fn($a) => $a->user_id . '|' . $a->test_sheet_id);

                        // 학생 목록 + 제출 정보 매핑
                        $rows = $targetStudents->map(function ($student) use ($answers, $wrongAnswerSheets, $wrongAnswerAnswers) {
                            $userId = $student->user?->id;
                            $answer = $userId ? ($answers[$userId] ?? null) : null;
                            $wrongSheet = $userId ? ($wrongAnswerSheets[$userId] ?? null) : null;
                            $wrongAnswerKey = $wrongSheet && $userId ? ($userId . '|' . $wrongSheet->test_sheet_id) : null;
                            $wrongAnswer = $wrongAnswerKey && $wrongAnswerAnswers->has($wrongAnswerKey)
                                ? $wrongAnswerAnswers[$wrongAnswerKey]->first()
                                : null;

                            return [
                                'student_name' => $student->user?->name ?? '-',
                                'classroom' => $student->classrooms->first()?->name ?? '-',
                                // 본문제
                                'main_submitted' => (bool) $answer,
                                'main_correct_count' => $answer?->correct_count,
                                'main_time' => $answer?->time,
                                'main_submitted_at' => $answer?->updated_at,
                                // 오답유사유형
                                'wrong_assigned' => (bool) $wrongSheet,
                                'wrong_submitted' => (bool) $wrongAnswer,
                                'wrong_correct_count' => $wrongAnswer?->correct_count,
                                'wrong_time' => $wrongAnswer?->time,
                                'wrong_submitted_at' => $wrongAnswer?->updated_at,
                            ];
                        })->sortBy([
                            ['main_submitted', 'desc'],
                            ['student_name', 'asc'],
                        ])->values();

                        $mainSubmittedCount = $rows->where('main_submitted', true)->count();
                        $mainNotSubmittedCount = $totalCount - $mainSubmittedCount;
                        $wrongAssignedCount = $rows->where('wrong_assigned', true)->count();
                        $wrongSubmittedCount = $rows->where('wrong_submitted', true)->count();
                        $wrongNotSubmittedCount = $wrongAssignedCount - $wrongSubmittedCount;

                        return view('filament.components.modals.test-sheet-submission-status', [
                            'rows' => $rows,
                            'totalCount' => $totalCount,
                            'mainSubmittedCount' => $mainSubmittedCount,
                            'mainNotSubmittedCount' => $mainNotSubmittedCount,
                            'wrongAssignedCount' => $wrongAssignedCount,
                            'wrongSubmittedCount' => $wrongSubmittedCount,
                            'wrongNotSubmittedCount' => $wrongNotSubmittedCount,
                            'testSheet' => $record,
                        ]);
                    }),
                Tables\Actions\Action::make('end-retry-test-sheets')
                    ->label('오답테스트 마감')
                    ->color('danger')
                    ->visible(function ($record) {
                        if ($record->academy_id !== auth()->user()->academy_id) {
                            return false;
                        }
                        return WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                            ->where('retry_count', 2)
                            ->whereHas('testSheet', function ($query) {
                                $query->where('status', 'progress');
                            })
                            ->exists();
                    })
                    ->icon('heroicon-m-check')
                    ->modalHeading('오답테스트 마감')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        TestSheet::query()
                            ->whereHas('wrongAnswerTestSheets', function ($query) use ($record) {
                                $query->where('original_test_sheet_id', $record->id)
                                    ->where('retry_count', 2);
                            })
                            ->where('status', 'progress')
                            ->get()
                            ->each(function ($testSheet) {
                                $testSheet->complete();
                            });
                    }),
                Tables\Actions\Action::make('view-report-card')
                    ->label('성적표')
                    ->icon('heroicon-m-newspaper')
                    ->modalHeading(null)
                    ->modalSubmitAction(false)
                    ->form(function ($record) {
                        return [
                            Tabs::make('Tabs')
                                ->tabs([
                                    Tab::make('Tab1')
                                        ->label('문제지')
                                        ->schema([
                                            View::make('filament.components.modals.test-sheet-report-card-modal')
                                                ->viewData([
                                                    'record' => $record,
                                                ]),
                                        ]),
                                    Tab::make('Tab2')
                                        ->label('오답 문풀 분석표')
                                        ->columns(4)
                                        ->visible(function ($record) {

                                            // 2차 오답 테스트들을 조회
                                            $secondRetryTests = WrongAnswerTestSheet::query()
                                                ->where('original_test_sheet_id', $record->id)
                                                ->where('retry_count', 2)
                                                ->with('testSheet')
                                                ->get();

                                            // 2차 오답 테스트가 없으면 보이지 않음
                                            if ($secondRetryTests->isEmpty()) {
                                                return false;
                                            }

                                            // 모든 2차 오답 테스트가 completed 상태인지 확인
                                            return $secondRetryTests->every(function ($retryTest) {
                                                return $retryTest->testSheet->status === 'completed';
                                            });
                                        })
                                        ->schema([
                                            Select::make('student_id')
                                                ->options(function ($record) {
                                                    return $record->getTargetStudents()
                                                        ->map(
                                                            fn($student) => ['id' => $student->id, 'name' => $student->user->name]
                                                        )
                                                        ->pluck('name', 'id');
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('학생 선택')
                                                ->label('학생')
                                                ->live()
                                                ->afterStateUpdated(function ($livewire, $state) {
                                                    $livewire->dispatch('studentChanged', $state);
                                                }),
                                            View::make('filament.components.modals.test-sheet-wrong-report-card-modal')
                                                ->viewData([
                                                    'record' => $record,
                                                ])->columnSpanFull(),
                                        ])
                                ])
                        ];
                    })
                    // ->modalContent(fn($record) => view('filament.components.modals.test-sheet-report-card-modal', [
                    //     'record' => $record,
                    // ]))
                    ->visible(fn($record) => $record->status === 'completed')
                    ->modalWidth('6xl'),
                Tables\Actions\Action::make('edit-test-sheet')
                    ->label('수정')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn($record) => '/admin/test-sheets/create/' . $record->temp_data_id . '?test_sheet_id=' . $record->id)
                    ->visible(fn($record) => $record->academy_id === auth()->user()->academy_id && $record->status === 'pending'),
                ActionGroup::make([
                    Tables\Actions\Action::make('share-settings')
                        ->label('공유 설정')
                        ->icon('heroicon-m-share')
                        ->visible(fn($record) =>
                            $record->academy_id === auth()->user()->academy_id &&
                            in_array(auth()->user()->role, ['root_admin', 'admin']) &&
                            in_array($record->source_type, ['mock_exam', 'school_exam'])
                        )
                        ->modalHeading('문제지 공유 설정')
                        ->modalWidth('lg')
                        ->fillForm(fn($record) => [
                            'share_scope' => $record->share_scope ?? 'academy',
                            'allowed_academy_ids' => $record->permissions()
                                ->where('is_allowed', true)
                                ->pluck('academy_id')
                                ->toArray(),
                        ])
                        ->form([
                            Forms\Components\Radio::make('share_scope')
                                ->label('공유 범위')
                                ->options([
                                    'academy' => '내 학원만 (비공개)',
                                    'all' => '전체 학원 공개',
                                    'restricted' => '선택 학원만 공개',
                                ])
                                ->default('academy')
                                ->live()
                                ->required(),
                            Forms\Components\CheckboxList::make('allowed_academy_ids')
                                ->label('공개할 학원')
                                ->options(function () {
                                    return Academy::where('id', '!=', auth()->user()->academy_id)
                                        ->where('is_active', true)
                                        ->pluck('name', 'id');
                                })
                                ->visible(fn(Forms\Get $get) => $get('share_scope') === 'restricted')
                                ->columns(2),
                        ])
                        ->action(function ($record, array $data) {
                            $shareScope = $data['share_scope'] === 'academy' ? null : $data['share_scope'];
                            $record->update(['share_scope' => $shareScope]);

                            // restricted인 경우 permissions 동기화
                            $record->permissions()->delete();
                            if ($data['share_scope'] === 'restricted' && !empty($data['allowed_academy_ids'])) {
                                foreach ($data['allowed_academy_ids'] as $academyId) {
                                    $record->permissions()->create([
                                        'academy_id' => $academyId,
                                        'is_allowed' => true,
                                    ]);
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('공유 설정이 저장되었습니다.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('삭제')
                        ->modalHeading('시험지 삭제')
                        ->icon('heroicon-m-trash')
                        ->visible(fn($record) => $record->academy_id === auth()->user()->academy_id && $record->status === 'pending'),
                    Tables\Actions\Action::make('print-test-sheet')
                        ->label('문제지 출력')
                        ->icon('heroicon-m-printer')
                        ->url(fn($record) => '/admin/test-sheet/print?test_sheet_id=' . $record->id)
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('print-second-test-sheet')
                        ->icon('heroicon-m-printer')
                        ->label('오답 테스트 출력')
                        ->modalWidth('md')
                        ->modalContent(fn($record) => view('filament.components.modals.print-second-test-sheet-modal', [
                            'record' => $record,
                        ]))
                        ->visible(function ($record) {
                            return WrongAnswerTestSheet::where('original_test_sheet_id', $record->id)
                                ->where('retry_count', 2)
                                ->exists();
                        })
                        ->modalSubmitActionLabel(false)
                        ->modalSubmitAction(false),
                    Tables\Actions\Action::make('copy-test-sheet')
                        ->label('문제지 복제')
                        ->icon('heroicon-m-document-duplicate')
                        ->url(fn($record) => '/admin/test-sheets/create/' . $record->temp_data_id . '?test_sheet_id=' . $record->id . '&copy=true'),
                    Tables\Actions\Action::make('assign-teachers')
                        ->label('강사 할당')
                        ->icon('heroicon-m-document-text')
                        ->modalHeading('강사 할당')
                        ->visible(fn($record) => $record->academy_id === auth()->user()->academy_id && auth()->user()->role !== 'general')
                        ->form([
                            Select::make('teacher_ids')
                                ->label('강사')
                                ->options(function () {
                                    return \App\Models\Teacher::where('role', 'general')->with('user')
                                        ->get()
                                        ->mapWithKeys(function ($teacher) {
                                            return [$teacher->id => $teacher->user->name];
                                        });
                                })
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->placeholder('강사 선택')
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            // 기존에 할당된 강사들 확인
                            $existingTeacherIds = $record->teachers()->pluck('teachers.id')->toArray();
                            $newTeacherIds = $data['teacher_ids'];

                            // 중복되지 않는 새로운 강사들만 필터링
                            $uniqueTeacherIds = array_diff($newTeacherIds, $existingTeacherIds);

                            if (empty($uniqueTeacherIds)) {
                                // 모든 강사가 이미 할당된 경우
                                \Filament\Notifications\Notification::make()
                                    ->title('이미 할당된 강사들입니다.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // 중복되지 않는 강사들만 할당
                            $record->teachers()->attach($uniqueTeacherIds);

                            // 중복된 강사가 있었는지 확인
                            $duplicateCount = count($newTeacherIds) - count($uniqueTeacherIds);

                            if ($duplicateCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("강사 할당 완료")
                                    ->body("{$duplicateCount}명의 강사는 이미 할당되어 있습니다.")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('강사에게 할당되었습니다.')
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('시험지 삭제')
                ]),
            ])
            ->emptyStateHeading('출제된 시험이 없습니다.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function formatGradeTarget(array $grades): string
    {
        $gradeNames = array_map(function ($gradeId) {
            $grade = GradeSystem::find($gradeId);
            return $grade->display_name;
        }, $grades);

        return implode(', ', $gradeNames) . ' - 학년 전체';
    }

    public static function formatLevelTarget(array $grades, array $levels): string
    {
        $gradeNames = array_map(function ($gradeId) {
            $grade = GradeSystem::find($gradeId);
            return $grade?->display_name;
        }, $grades);

        // null이나 빈 값 제거
        $gradeNames = array_filter($gradeNames);
        $levels = array_filter($levels);

        if (empty($gradeNames) || empty($levels)) {
            return '';
        }

        // "중1, 중2 - A레벨, B레벨" 형태로 출력
        return implode(', ', $gradeNames) . ' - ' .
            implode('레벨, ', $levels) . '레벨';
    }

    public static function formatClassroomTarget(array $classroomIds): string
    {
        $classroomNames = array_map(function ($classroomId) {
            $classroom = Classroom::find($classroomId);
            return $classroom?->name;
        }, $classroomIds);

        // null이나 빈 값 제거
        $classroomNames = array_filter($classroomNames);

        if (empty($classroomNames)) {
            return '';
        }

        return implode(', ', $classroomNames);
    }

    public static function formatStudentTarget(array $studentIds): string
    {
        $studentNames = array_map(function ($studentId) {
            $student = User::find($studentId);
            return $student?->name;
        }, $studentIds);

        // null이나 빈 값 제거
        $studentNames = array_filter($studentNames);

        if (empty($studentNames)) {
            return '';
        }

        // 각 학생 이름 뒤에 "학생" 붙이기
        $formattedNames = array_map(function ($name) {
            return $name . ' 학생';
        }, $studentNames);

        return implode(', ', $formattedNames);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestSheets::route('/'),
            // 'create' => Pages\CreateTestSheet::route('/create'),
            // 'edit' => Pages\EditTestSheet::route('/{record}/edit'),
        ];
    }
}
