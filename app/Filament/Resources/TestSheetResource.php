<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestSheetResource\Pages;
use App\Filament\Resources\TestSheetResource\RelationManagers;
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
                TextColumn::make('tag')
                    ->label('태그')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('시험지 명')
                    ->sortable()
                    ->searchable()
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return <<<EOF
                            $record->name <br />
                            <span class="text-primary-500 mt-1 font-medium">14문항 |</span>
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
                            return implode(',', $record->target_grades) . ' - 학년 전체';
                        } else if ($record->target_group === 'level') {
                            return $record->target_grades[0] . ' - '  .  $record->target_levels[0] . '레벨';
                        } else if (
                            $record->target_group === 'classroom'
                        ) {
                            return  implode(',<br />', $record->target_classrooms);
                        }
                    }),
                // TextColumn::make('target_group')
                //     ->badge()
                //     ->label('')
                //     ->formatStateUsing(function ($record) {
                //         if ($record->target_group === 'grade') {
                //             return '학년 전체';
                //         } else if ($record->target_group === 'level') {
                //             return '레벨별';
                //         } else if (
                //             $record->target_group === 'classroom'
                //         ) {
                //             return '반별';
                //         } else {
                //             return '학생별';
                //         }
                //     })
                //     ->color(fn(string $state): string => match ($state) {
                //         default => 'gray',
                //     }),


                TextColumn::make('created_at')
                    ->date('Y-m-d')
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
                Tables\Actions\Action::make('view-report-card')
                    ->label('성적표')
                    ->icon('heroicon-m-newspaper')
                    ->modalHeading('결과 조회')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.components.modals.test-sheet-report-card-modal', [
                        'record' => $record,
                    ]))
                    ->modalWidth('5xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
