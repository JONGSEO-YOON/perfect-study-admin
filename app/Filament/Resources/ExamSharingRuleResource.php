<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamSharingRuleResource\Pages;
use App\Models\Academy;
use App\Models\ExamSharingRule;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamSharingRuleResource extends Resource
{
    protected static ?string $model = ExamSharingRule::class;

    protected static ?string $navigationLabel = '기출 공유 권한';

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 11;

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'root_admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Select::make('academy_id')
                    ->label('대상 학원')
                    ->options(Academy::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('source_type')
                    ->label('문제 출처')
                    ->options([
                        'mock_exam' => '모의고사 기출',
                        'school_exam' => '학교 기출',
                    ])
                    ->required()
                    ->live(),
                Select::make('exam_year')
                    ->label('년도 (비워두면 전체)')
                    ->options(array_combine(range(date('Y'), 2010, -1), range(date('Y'), 2010, -1)))
                    ->searchable()
                    ->placeholder('전체 년도'),
                Select::make('exam_month')
                    ->label('월 (모의고사)')
                    ->options([3 => '3월', 4 => '4월', 6 => '6월', 7 => '7월', 9 => '9월', 10 => '10월', 11 => '11월'])
                    ->placeholder('전체')
                    ->visible(fn(Get $get) => $get('source_type') === 'mock_exam'),
                Select::make('school_id')
                    ->label('학교 (학교기출)')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array =>
                        \App\Models\School::where('name', 'like', "%{$search}%")
                            ->limit(50)->pluck('name', 'id')->toArray()
                    )
                    ->getOptionLabelUsing(fn($value): ?string =>
                        \App\Models\School::find($value)?->name
                    )
                    ->placeholder('전체')
                    ->visible(fn(Get $get) => $get('source_type') === 'school_exam'),
                Select::make('exam_type')
                    ->label('시험 유형 (학교기출)')
                    ->options(['midterm' => '중간고사', 'final' => '기말고사'])
                    ->placeholder('전체')
                    ->visible(fn(Get $get) => $get('source_type') === 'school_exam'),
                Toggle::make('is_allowed')
                    ->label('접근 허용')
                    ->default(true)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academy.name')->label('학원'),
                TextColumn::make('source_type')
                    ->label('출처')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'mock_exam' => '모의고사',
                        'school_exam' => '학교기출',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'mock_exam' => 'info',
                        'school_exam' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('exam_year')->label('년도')->placeholder('전체'),
                TextColumn::make('exam_month')->label('월')->placeholder('전체'),
                TextColumn::make('school.name')->label('학교')->placeholder('전체'),
                TextColumn::make('exam_type')
                    ->label('시험')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'midterm' => '중간',
                        'final' => '기말',
                        default => '',
                    })
                    ->placeholder('전체'),
                IconColumn::make('is_allowed')->label('허용')->boolean(),
            ])
            ->filters([
                SelectFilter::make('academy_id')
                    ->label('학원')
                    ->options(Academy::pluck('name', 'id')),
                SelectFilter::make('source_type')
                    ->label('출처')
                    ->options(['mock_exam' => '모의고사', 'school_exam' => '학교기출']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamSharingRules::route('/'),
            'create' => Pages\CreateExamSharingRule::route('/create'),
            'edit' => Pages\EditExamSharingRule::route('/{record}/edit'),
        ];
    }
}
