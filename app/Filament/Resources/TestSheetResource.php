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

class TestSheetResource extends Resource
{
    protected static ?string $model = TestSheet::class;

    protected static ?string $navigationLabel = '문제지 관리';

    protected static ?string $title = '문제지 관리';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = '교실 관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function canViewAny(): bool
    {
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
                return $query->originals()
                    ->when(auth()->user()->role === 'general', function ($query) {
                        return $query->where('user_id', auth()->user()->id);
                    });
                // ->where('user_id', auth()->user()->id);
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
                TextColumn::make('tags')
                    ->label('태그')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('name')
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
                    ->searchable()
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
                    ->visible(fn($record) => $record->status === 'pending' && empty($record->start_date))
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
                    ->visible(fn($record) => $record->status === 'progress')
                    ->icon('heroicon-m-check')
                    ->modalHeading('문제지 마감')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->complete()),
                Tables\Actions\Action::make('end-retry-test-sheets')
                    ->label('오답테스트 마감')
                    ->color('danger')
                    ->visible(function ($record) {
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
                    ->visible(fn($record) => $record->status === 'pending'),
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->label('삭제')
                        ->modalHeading('시험지 삭제')
                        ->icon('heroicon-m-trash')
                        ->visible(fn($record) => $record->status === 'pending'),
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
                        ->icon('heroicon-m-share')
                        ->modalHeading('강사 할당')
                        ->form([
                            Select::make('teacher_ids')
                                ->label('강사')
                                ->options(function () {
                                    return \App\Models\Teacher::where('role', '!=', 'root_admin')->with('user')
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
                            // 해당 문제지를 할당한 강사들에게 문제지 할당

                            \Filament\Notifications\Notification::make()
                                ->title('강사에게 할당되었습니다.')
                                ->success()
                                ->send();
                        })
                        ->hidden(true),
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
