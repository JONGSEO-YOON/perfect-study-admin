<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestSheetResource\Pages;
use App\Filament\Resources\TestSheetResource\RelationManagers;
use App\Models\Classroom;
use App\Models\GradeSystem;
use App\Models\Student;
use App\Models\TestSheet;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('tags')
                    ->label('태그')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('시험지 명')
                    ->sortable()
                    ->searchable()
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $question_count = count($record->questions);
                        return <<<EOF
                            $record->name <br />
                            <span class="text-primary-500 mt-1 font-medium">{$question_count}문항 |</span>
                            <span class="text-primary-500 mt-1 font-semibold">{$record->scopes[0]}</span>
                            
                        EOF;
                    }),
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
                        $auto = $record->is_auto ? '(자동 출제)' : '';
                        if ($record->status === 'pending') {
                            return '출제 대기 ' . $auto;
                        } else if ($record->status === 'progress') {
                            return '출제 중 ' . $auto;
                        } else if ($record->status === 'completed') {
                            return '출제 종료 ' . $auto;
                        }
                    })
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'draft' => 'gray',
                        'progress' => 'primary',
                        'completed' => 'success',
                        default => 'gray',
                    }),
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
                Tables\Actions\Action::make('end-test-sheet')
                    ->label('문제지 마감')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'progress')
                    ->icon('heroicon-m-check')
                    ->modalHeading('문제지 마감')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->complete()),
                Tables\Actions\Action::make('view-report-card')
                    ->label('성적표')
                    ->icon('heroicon-m-newspaper')
                    ->modalHeading('결과 조회')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.components.modals.test-sheet-report-card-modal', [
                        'record' => $record,
                    ]))
                    ->visible(fn($record) => $record->status === 'completed')
                    ->modalWidth('6xl'),
                Tables\Actions\Action::make('edit-test-sheet')
                    ->label('수정')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn($record) => '/admin/test-sheets/create/' . $record->temp_data_id . '?test_sheet_id=' . $record->id)
                    ->visible(fn($record) => $record->status === 'pending'),
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
