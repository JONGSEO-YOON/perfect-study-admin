<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamSeriesResource\Pages;
use App\Models\ExamSeries;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamSeriesResource extends Resource
{
    protected static ?string $model = ExamSeries::class;

    protected static ?string $navigationLabel = '모의고사 문제계열';

    protected static ?string $modelLabel = '문제계열';

    protected static ?string $pluralModelLabel = '문제계열';

    protected static ?string $navigationGroup = '설정';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['root_admin', 'admin']);
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('계열 명칭')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->placeholder('예: 통합형, 미적분Ⅱ 등'),
            TextInput::make('sort_order')
                ->label('정렬 순서')
                ->numeric()
                ->default(0)
                ->helperText('숫자가 작을수록 먼저 노출됩니다.'),
            Toggle::make('is_active')
                ->label('사용')
                ->default(true)
                ->helperText('비활성화 시 문제 등록/검색 옵션에서 숨김 처리됩니다.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('순서')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('계열')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('사용')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
            'index' => Pages\ListExamSeries::route('/'),
        ];
    }
}
