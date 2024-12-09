<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestSheetResource\Pages;
use App\Filament\Resources\TestSheetResource\RelationManagers;
use App\Models\GradeSystem;
use App\Models\TestSheet;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
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
                            // loop through the target_grades and find the grade name
                            $grades = [];
                            foreach ($record->target_grades as $grade) {
                                $_grade = GradeSystem::find($grade);
                                $grades[] = $_grade->display_name;
                            }
                            return implode(', ', $grades) . ' - 학년 전체';
                        } else if ($record->target_group === 'level') {
                            return $record->target_grades[0] . ' - '  .  $record->target_levels[0] . '레벨';
                        } else if (
                            $record->target_group === 'classroom'
                        ) {
                            return  implode(',<br />', $record->target_classrooms);
                        }
                    }),
                TextColumn::make('start_date')
                    ->date('Y-m-d H:i')
                    ->label('출제일')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('출제 상태')
                    ->formatStateUsing(function ($record) {
                        if ($record->status === 'pending') {
                            return '출제 대기';
                        } else if ($record->status === 'draft') {
                            return '미출제';
                        } else if ($record->status === 'progress') {
                            return '출제 중';
                        } else if ($record->status === 'completed') {
                            return '출제 종료';
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
                //
                Filter::make('duration')
                    ->columnSpan(2)
                    ->form([
                        Fieldset::make('duration')
                            ->label('시험 조회 기간')
                            ->extraAttributes(['class' => 'border-none', 'style' => 'padding: 0; padding-top: 0.5rem;'])
                            ->schema([
                                DatePicker::make('from')
                                    ->default(now()->format('Y-m-d'))
                                    ->label(false),
                                DatePicker::make('until')
                                    ->default(now()->format('Y-m-d'))
                                    ->label(false)
                            ]),
                    ])
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
                    ->action(fn($record) => $record->update([
                        'status' => 'completed',
                        'end_date' => now(),
                    ])),
                Tables\Actions\Action::make('view-report-card')
                    ->label('성적표')
                    ->icon('heroicon-m-newspaper')
                    ->modalHeading('결과 조회')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.components.modals.test-sheet-report-card-modal', [
                        'record' => $record,
                    ]))
                    ->visible(fn($record) => $record->status === 'completed')
                    ->modalWidth('5xl'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestSheets::route('/'),
            // 'create' => Pages\CreateTestSheet::route('/create'),
            // 'edit' => Pages\EditTestSheet::route('/{record}/edit'),
        ];
    }
}
